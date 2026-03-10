<?php

namespace App\modules\address\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\CURLRequest;

class Address extends BaseController
{

    protected $client;

    public function __construct()
    {
        $this->client = \Config\Services::curlrequest();
    }

    public function get_desa_api($keyword = '')
    {
        try {
            $url = "https://api.wilayah.id/desa?search=" . $keyword;

            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 10 // Set timeout agar aplikasi tidak hang
            ]);
            if ($response->getStatusCode() === 200) {
                return $this->response->setJSON(json_decode($response->getBody()));
            }
            return $this->response->setJSON(['status' => false, 'message' => 'API Error']);
            
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
