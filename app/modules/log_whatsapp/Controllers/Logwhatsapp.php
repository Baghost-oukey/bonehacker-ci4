<?php

namespace App\modules\log_whatsapp\Controllers;

use App\Controllers\BaseController;
use App\modules\log_whatsapp\Models\MLogWhatsapp;
use App\modules\whatsapp\Models\MWhatsapp;
use CodeIgniter\HTTP\ResponseInterface;

class Logwhatsapp extends BaseController
{

    protected $model_log_whatsapp;
    protected $model_whatsapp;
    protected $session;

    public function __construct()
    {
        $this->model_log_whatsapp = new MLogWhatsapp();
        $this->model_whatsapp = new MWhatsapp();
        $this->session = \Config\Services::session();
    }
    public function index()
    {
        $logs = $this->model_log_whatsapp->paginate(10, 'group1');
        //
        $data = [
            'role' => $this->session->get('role'),
            'realname' => $this->session->get('realname'),
            'base_url' => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title' => 'Log WhatsApp',
            'logs' => $logs,
            'pager'=> $this->model_log_whatsapp->pager,
            'msg' => $this->session->getFlashdata('message')
        ];

        return view('App\modules\log_whatsapp\Views\views_log_whatsapp', $data);
    }

    public function sendWhatsAppMessage($phone, $message, $instance_id, $token)
    {
        $client = \Config\Services::curlrequest();

        try {
            // Parameter dikirim via Query String (GET) sesuai API MeoBlaster di kodingan CI3 Anda
            $response = $client->request('GET', 'https://app.meoblaster.com/api/send', [
                'query' => [
                    'number'       => $phone,
                    'type'         => 'text',
                    'message'      => $message,
                    'instance_id'  => $instance_id,
                    'access_token' => $token
                ],
                'http_errors' => false,
                'verify'      => false
            ]);

            return $response->getBody();
        } catch (\Exception $e) {
            log_message('error', 'MeoBlaster Error: ' . $e->getMessage());
            return false;
        }
    }



    public function resend()
    {
        $log_id = $this->request->getPost('log_id');
        $log    = $this->model_log_whatsapp->find($log_id);

        if ($log && $log->is_sent == 0) {
            $whatsappData = $this->model_whatsapp->getMessageAndCredentials();

            if (!$whatsappData) {
                $this->session->setFlashdata('message', ['danger', 'Konfigurasi WhatsApp tidak ditemukan.']);
                return redirect()->to('whatsapp/log_whatsapp');
            }
            $response = $this->sendWhatsAppMessage(
                $log->phone,
                $log->message,
                $whatsappData->instance_id,
                $whatsappData->token
            );

            $response_data = json_decode($response, true);
            $is_sent = 0;
            $time_sent = null;

            // Logika pengecekan status sukses API MeoBlaster
            if (isset($response_data['status']) && $response_data['status'] === 'success') {
                $is_sent = 1;
                $time_sent = date('Y-m-d H:i:s');
            }

            $update_data = [
                'is_sent'    => $is_sent,
                'time_sent'  => $time_sent,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->model_log_whatsapp->update($log_id, $update_data);

            if ($is_sent) {
                $this->session->setFlashdata('message', ['success', 'Pesan berhasil dikirim ulang.']);
            } else {
                $this->session->setFlashdata('message', ['danger', 'Gagal mengirim ulang pesan. Cek koneksi API.']);
            }
        } else {
            $this->session->setFlashdata('message', ['danger', 'Pesan tidak ditemukan atau sudah berhasil dikirim.']);
        }

        return redirect()->to('whatsapp/log_whatsapp');
    }
}
