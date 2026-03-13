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

        $date = $this->request->getGet('date');

        if (!$date || !preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
            $date = date('Y-m-d');
        }

        $logFile = WRITEPATH . 'logs/log-' . $date . '.log';

        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
        } else {
            $logFilePhp = WRITEPATH . 'logs/log-' . $date . '.php';
            if (file_exists($logFilePhp)) {
                $logContent = file_get_contents($logFilePhp);
            } else {
                $logContent = "No logs found for the selected date: " . $date;
            }
        }

        $data = [
            'role'            => $this->session->get('role'),
            'realname'        => $this->session->get('realname'),
            'log_content'     => $logContent,
            'date'            => $date,
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'System Logs',
            'msg'             => $this->session->getFlashdata('message')
        ];

        return view('App\modules\logs\Views\views_logs', $data);
    }
}
