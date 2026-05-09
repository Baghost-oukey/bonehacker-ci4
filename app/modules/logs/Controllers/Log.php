<?php

namespace App\modules\logs\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Log extends BaseController
{

    protected $session;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        helper('file');
        $logMessage = "Logs accessed on " . date('Y-m-d H:i:s') . "\n";
        $accessLogPath = WRITEPATH . 'logs/access_log.txt';
        write_file($accessLogPath, $logMessage, 'a');
    }
    public function index()
    {
        if (!in_array($this->session->get('role'), ['superadmin', 'owner'])) {
            return redirect()->to('/')->with('message', ['danger', 'Akses ditolak!']);
        }

        $date = $this->request->getGet('date') ?? date('Y-m-d');

        // 1. Coba cari yang .log dulu (karena di folder kamu adanya ini)
        $logFile = WRITEPATH . 'logs/log-' . $date . '.log';

        // 2. Kalau .log nggak ada, coba cari yang .php
        if (!file_exists($logFile)) {
            $logFile = WRITEPATH . 'logs/log-' . $date . '.php';
        }

        $logContent = "";

        if (file_exists($logFile)) {
            $rawContent = file_get_contents($logFile);

            // Bersihkan header PHP jika ada (buat jaga-jaga kalau filenya .php)
            $rawContent = str_replace(["<?php", "defined('COREPATH') || exit('No direct script access allowed');"], "", $rawContent);

            $lines = explode("\n", $rawContent);
            $filteredLines = array_filter($lines, function ($line) {
                // Kita ambil baris INFO, ERROR, CRITICAL, dan DEBUG
                return str_contains($line, 'INFO') ||
                    str_contains($line, 'ERROR') ||
                    str_contains($line, 'CRITICAL') ||
                    str_contains($line, 'DEBUG');
            });

            $logContent = !empty($filteredLines) ? implode("\n", $filteredLines) : "File log ditemukan, tapi tidak ada baris aktivitas (INFO/ERROR).";
        } else {
            $logContent = "Log untuk tanggal $date tidak ditemukan di sistem.";
        }

        $data = [
            'role'            => $this->session->get('role'),
            'realname'        => $this->session->get('realname'),
            'log_content'     => trim($logContent),
            'date'            => $date,
            'title'           => 'Activity Logs',
        ];

        return view('App\modules\logs\Views\views_logs', $data);
    }
}
