<?php

namespace App\modules\rank_terapis\Controllers;

use App\Controllers\BaseController;
use App\modules\rank_terapis\Models\MRankTerapis;

class RankTerapis extends BaseController
{
    protected $rankModel;
    protected $session;

    public function __construct()
    {
        $this->rankModel = new MRankTerapis();
        $this->session = \Config\Services::session();
    }

    private function requireSuperadmin()
    {
        if ($this->session->get('role') !== 'superadmin') {
            $this->session->setFlashdata('error', 'Anda tidak memiliki akses ke halaman ini');
            return redirect()->to(base_url('beranda'));
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->requireSuperadmin()) {
            return $redirect;
        }

        $data = [
            'realname'        => $this->session->get('realname'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Pengaturan Rank Terapis',
            'msg'             => $this->session->getFlashdata('message'),
            'role'            => $this->session->get('role'),
            'ranks'           => $this->rankModel->getData(),
        ];

        return view('App\modules\rank_terapis\Views\index', $data);
    }

    public function store()
    {
        if ($redirect = $this->requireSuperadmin()) {
            return $redirect;
        }

        $name = strtoupper(trim((string) $this->request->getPost('name')));

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama rank wajib diisi');
        }

        if ($this->rankModel->existsByName($name)) {
            return redirect()->back()->withInput()->with('error', 'Nama rank sudah digunakan');
        }

        $now = date('Y-m-d H:i:s');
        $this->rankModel->insert([
            'name'        => $name,
            'description' => $this->request->getPost('description') ?: null,
            'sort_order'  => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'   => $this->request->getPost('is_active') === 'on' ? 1 : 0,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        return redirect()->to(base_url('rank-terapis'))->with('success', 'Rank terapis berhasil ditambahkan');
    }

    public function update($id)
    {
        if ($redirect = $this->requireSuperadmin()) {
            return $redirect;
        }

        $rank = $this->rankModel->find($id);
        if (!$rank) {
            return redirect()->to(base_url('rank-terapis'))->with('error', 'Rank tidak ditemukan');
        }

        $name = strtoupper(trim((string) $this->request->getPost('name')));

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama rank wajib diisi');
        }

        if ($this->rankModel->existsByName($name, (int) $id)) {
            return redirect()->back()->withInput()->with('error', 'Nama rank sudah digunakan');
        }

        $this->rankModel->update($id, [
            'name'        => $name,
            'description' => $this->request->getPost('description') ?: null,
            'sort_order'  => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'   => $this->request->getPost('is_active') === 'on' ? 1 : 0,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('rank-terapis'))->with('success', 'Rank terapis berhasil diperbarui');
    }

    public function destroy($id)
    {
        if ($redirect = $this->requireSuperadmin()) {
            return $redirect;
        }

        $rank = $this->rankModel->find($id);
        if (!$rank) {
            return redirect()->to(base_url('rank-terapis'))->with('error', 'Rank tidak ditemukan');
        }

        $used = $this->rankModel->db->table('terapis')->where('rank', $rank->name)->countAllResults();
        if ($used > 0) {
            return redirect()->to(base_url('rank-terapis'))->with('error', 'Rank masih dipakai oleh terapis');
        }

        $this->rankModel->delete($id);

        return redirect()->to(base_url('rank-terapis'))->with('success', 'Rank terapis berhasil dihapus');
    }
}
