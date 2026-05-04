<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function infort()
    {
        return response()->json([
            'message' => 'Ini adalah informasi khusus Ketua RT',
            'data' => [
                'kas_rt' => 10000000,
                'laporan_warga' => 5
            ]
        ]);
    }

    public function infowrg()
    {
        return response()->json([
            'message' => 'Ini adalah informasi khusus Warga',
            'data' => [
                'jadwal_ronda' => 'Senin malam',
                'iuran_bulanan' => 50000
            ]
        ]);
    }

    public function infobersama()
    {
        return response()->json([
            'message' => 'Ini adalah informasi bersama untuk Ketua RT dan Warga',
            'data' => [
                'pengumuman' => 'Kerja bakti hari Minggu jam 07:00 WIB',
                'peraturan' => 'Dilarang membuang sampah sembarangan'
            ]
        ]);
    }
}
