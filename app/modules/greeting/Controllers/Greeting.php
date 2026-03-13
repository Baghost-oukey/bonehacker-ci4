<?php

namespace App\modules\greeting\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Greeting extends BaseController
{

    private $jsonFile;
    protected $session;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->jsonFile = ROOTPATH . 'public/greetings/greetings.json';
    }

    private function loadGreetings()
    {
        if (file_exists($this->jsonFile)) {
            $jsonData = file_get_contents($this->jsonFile);
            return json_decode($jsonData, true) ?? [];
        }
        return [];
    }

    private function saveGreetings($greetings)
    {
        file_put_contents($this->jsonFile, json_encode($greetings, JSON_PRETTY_PRINT));
    }

    public function index()
    {
        $allGreetings = $this->loadGreetings();

        // --- Logika Pagination Manual (10 data per halaman) ---
        $pager = \Config\Services::pager();
        $page  = (int) ($this->request->getVar('page_group1') ?? 1);
        $Page = 10;
        $total   = count($allGreetings);

        // Ambil potongan array berdasarkan halaman
        $offset         = ($page - 1) * $Page;
        $pagedGreetings = array_slice($allGreetings, $offset, $Page, true);

        $pagerLinks = $pager->makeLinks($page, $Page, $total, 'default_full', 0, 'group1');

        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Sapaan',
            'greetings'       => $pagedGreetings, // Kirim data yang sudah dipotong
            'pager'           => $pagerLinks,
            'msg'             => $this->session->getFlashdata('message'),
        ];

        return view('App\modules\greeting\Views\views_greeting', $data);
    }

    public function save()
    {
        $greetings = $this->loadGreetings();

        $greetingText  = $this->request->getPost('greetings');
        $greetingIndex = $this->request->getPost('greeting_index');

        // Olah string ke array
        $greetingsArray = array_filter(array_map('trim', explode("\n", $greetingText)));

        if ($greetingIndex !== null && isset($greetings[$greetingIndex])) {
            // Update spesifik
            $greetings[$greetingIndex] = trim($greetingText);
        } else {
            // Tambah baru
            $greetings = array_merge($greetings, $greetingsArray);
        }

        if (!empty($greetings)) {
            $this->saveGreetings(array_values($greetings));
            $this->session->setFlashdata('message', ['success', 'Sapaan berhasil disimpan']);
        }

        return redirect()->to('greeting');
    }

    public function delete($index)
    {
        $greetings = $this->loadGreetings();

        if (isset($greetings[$index])) {
            unset($greetings[$index]);
            $this->saveGreetings(array_values($greetings));
            $this->session->setFlashdata('message', ['success', 'Sapaan berhasil dihapus']);
        }

        return redirect()->to('greeting');
    }
}
