<?php

namespace App\modules\whatsapp\Controllers;

use App\Controllers\BaseController;
use App\modules\whatsapp\Models\MWhatsapp;
use CodeIgniter\HTTP\ResponseInterface;

class Whatsapp extends BaseController
{

    protected $model_whatsapp;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->model_whatsapp = new MWhatsapp();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
    }
    public function index()
    {
        //
        $data = [
            'realname' => $this->session->get('realname'),
            'role' => $this->session->get('role'),
            'base_url' => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title' => 'WhatsApp',
            'records' => $this->model_whatsapp->findAll(),
            'msg' => $this->session->getFlashdata('message')
        ];

        return view('App\modules\whatsapp\Views\views_whatsapp', $data);
    }

    public function store()
    {
        $data = [
            'url_api' => $this->request->getPost('url_api'),
            'instance_id' => $this->request->getPost('instance_id'),
            'token' => $this->request->getPost('token'),
            'message' => $this->request->getPost('message'),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        if ($this->model_whatsapp->insert($data)) {
            $this->session->setFlashdata('message', ['success', 'Data Berhasil diSimpan']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Failed to save data']);
        }

        return redirect()->to('whatsapp');
    }

    public function edit($id)
    {
        $data = [
            'url_api'     => $this->request->getPost('url_api'),
            'instance_id' => $this->request->getPost('instance_id'),
            'token'       => $this->request->getPost('token'),
            'message'     => $this->request->getPost('message'),
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        if ($this->model_whatsapp->update($id, $data)) {
            $this->session->setFlashdata('message', ['success', 'Data Berhasil diperbarui']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Failed to update data']);
        }

        return redirect()->to('whatsapp');
    }

    public function delete($id)
    {
        if ($this->model_whatsapp->delete($id)) {
            $this->session->setFlashdata('message', ['success', 'Data Berhasil dihapus']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Failed to delete data']);
        }

        return redirect()->to('whatsapp');
    }

    protected function _send_wa_post($phone, $message)
    {
        $config = $this->model_whatsapp->first();

        if (!$config) return false;

        $client = \Config\Services::curlrequest();

        try {
            $response = $client->request('POST', $config->url_api, [
                'form_params' => [
                    'instance_id' => $config->instance_id,
                    'token'       => $config->token,
                    'phone'       => $phone,
                    'message'     => $message
                ],
                'verify' => false
            ]);

            return $response->getBody();
        } catch (\Exception $e) {
            return "Curl Error: " . $e->getMessage();
        }
    }

    public function send_notif_patients($id_patients)
    {
        $patients = $this->db->table('patients')->getWhere(['id' => $id_patients])->getRow();
        if (!$patients) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data Pasien tidak ditemukan']);
        }
        $config = $this->model_whatsapp->first();
        $message_users = str_replace('[nama]', $patients->nama, $config->message);
        $result = $this->_send_wa_post($patients->phone, $message_users);
        return $this->response->setJSON(['result' => $result]);
    }
}
