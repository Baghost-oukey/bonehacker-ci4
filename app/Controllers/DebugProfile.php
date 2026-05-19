<?php

namespace App\Controllers;

class DebugProfile extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        $debug = [
            'session_data' => [
                'userId' => session()->get('userId'),
                'username' => session()->get('username'),
                'realname' => session()->get('realname'),
                'role' => session()->get('role'),
                'terapis_id' => session()->get('terapis_id'),
                'terapis_id_int' => session()->get('terapis_id_int'),
                'avatar_url' => session()->get('avatar_url'),
                'avatar' => session()->get('avatar'),
            ],
            'terapis_data' => null,
            'rank_data' => null,
        ];
        
        $terapisId = session()->get('terapis_id_int');
        if ($terapisId) {
            $terapis = $db->table('terapis')
                ->select('*')
                ->where('id', $terapisId)
                ->get()
                ->getRow();
            
            $debug['terapis_data'] = $terapis;
            
            if ($terapis && $terapis->rank) {
                // Try to get rank as ID
                if (is_numeric($terapis->rank)) {
                    $rankData = $db->table('rank_terapis')
                        ->select('*')
                        ->where('id', $terapis->rank)
                        ->get()
                        ->getRow();
                    $debug['rank_data'] = $rankData;
                }
            }
            
            // Check foto file
            if ($terapis && $terapis->foto) {
                $fotoPath = FCPATH . 'foto_karyawan/' . $terapis->foto;
                $debug['foto_exists'] = file_exists($fotoPath);
                $debug['foto_path'] = $fotoPath;
                $debug['foto_url'] = base_url('foto_karyawan/' . $terapis->foto);
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($debug, JSON_PRETTY_PRINT);
        exit;
    }
}
