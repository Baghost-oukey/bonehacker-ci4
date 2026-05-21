<?php

namespace App\Modules\transaksi\Controllers;

use App\Controllers\BaseController;
use App\Modules\transaksi\Models\MFinanceCategory;
use App\modules\region\Models\MRegion;

class FinanceCategoryController extends BaseController
{
    protected $mCategory;
    protected $mRegion;

    public function __construct()
    {
        $this->mCategory = new MFinanceCategory();
        $this->mRegion = new MRegion();
    }

    public function index()
    {
        $role = session()->get('role');
        if (!in_array($role, ['superadmin', 'owner'])) {
            return redirect()->to('/beranda')->with('error', 'Unauthorized access');
        }

        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;
        
        $regionId = $this->request->getGet('region_id') ?? (($region_patient !== 'all') ? $region_patient : 'all');

        $data = [
            'title'      => 'Master Kategori Keuangan',
            'categories' => $this->mCategory->getCategories(null, $regionId),
            'regions'    => $this->mRegion->getData(null, $allowed_regions),
            'filter_region' => $regionId,
            'role'       => $role
        ];

        return view('App\modules\transaksi\Views\categories\index', $data);
    }

    public function store()
    {
        $role = session()->get('role');
        if (!in_array($role, ['superadmin', 'owner'])) {
            return redirect()->to('/beranda')->with('error', 'Unauthorized access');
        }

        $region_patient = session()->get('region_patient');

        $name = $this->request->getPost('name');
        $type = $this->request->getPost('type');
        $target_region = $this->request->getPost('region_id');

        // Security check
        if ($role !== 'superadmin') {
            // Owner can only add for their own region
            $activeRegion = session()->get('active_region');
            if ($activeRegion && $activeRegion !== 'all') {
                $target_region = (int)$activeRegion;
            } else {
                $target_region = session()->get('region_id');
                if (empty($target_region) && is_array($region_patient) && !empty($region_patient)) {
                    $target_region = $region_patient[0];
                }
            }
        } else {
            // Superadmin can choose region or set to NULL (Global)
            if ($target_region === 'global') {
                $target_region = null;
            }
        }

        $this->mCategory->insert([
            'name'       => $name,
            'type'       => $type,
            'region_id'  => $target_region,
            'is_default' => 0
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function delete($id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['superadmin', 'owner'])) {
            return redirect()->to('/beranda')->with('error', 'Unauthorized access');
        }

        $category = $this->mCategory->find($id);
        if (!$category) {
            return redirect()->back()->with('error', 'Kategori tidak ditemukan.');
        }

        // System defaults cannot be deleted
        if ($category['is_default'] == 1) {
            return redirect()->back()->with('error', 'Kategori bawaan sistem tidak dapat dihapus.');
        }

        $region_patient = session()->get('region_patient');

        // Permission check
        if ($role !== 'superadmin') {
            $allowedRegions = is_array($region_patient) ? $region_patient : [$region_patient];
            if (!in_array($category['region_id'], $allowedRegions)) {
                return redirect()->back()->with('error', 'Unauthorized access');
            }
        }

        $this->mCategory->delete($id);
        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }
}
