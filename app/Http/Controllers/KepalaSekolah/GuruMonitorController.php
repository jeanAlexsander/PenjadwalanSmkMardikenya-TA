<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guru;

class GuruMonitorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 14);
        $guru = Guru::with('user')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
        return view('kepala_sekolah.guru.index', compact('guru'));
    }
}
