<?php

namespace App\Http\Controllers\Pkl;

use App\Exceptions\UserError;
use App\Http\Controllers\Controller;
use App\Services\ImporMaster;
use App\Support\Xlsx;
use Illuminate\Http\Request;

/** Impor DUDI & pembimbing industri lama dari Excel: unduh templat, isi, "Periksa saja", lalu Impor. Admin & koordinator. */
class ImporController extends Controller
{
    public function index()
    {
        return view('master.impor');
    }

    public function templat()
    {
        return Xlsx::download('TEMPLAT_DATA_PKL_LAMA.xlsx', ImporMaster::templat());
    }

    public function unggah(Request $request)
    {
        $request->validate(['berkas' => 'required|file|max:5120|mimes:xlsx', 'uji' => 'nullable|boolean'],
            ['berkas.required' => 'Pilih berkas Excel hasil isian templat.', 'berkas.mimes' => 'Berkas harus .xlsx (templat dari halaman ini).']);
        $f = $request->file('berkas');
        $uji = $request->boolean('uji');
        try {
            $h = ImporMaster::proses($this->me(), $f->getRealPath(), $f->getClientOriginalName(), $uji);
        } catch (UserError $e) {
            $baris = explode("\n", $e->getMessage());

            return back()->with('error', array_shift($baris))->with('galat', $baris);
        }
        $pesan = ($uji ? 'Periksa saja (belum disimpan): ' : 'Impor selesai: ')."{$h['dudi']} DUDI, {$h['pembimbing']} pembimbing industri"
            .($h['dilewati'] ? "; {$h['dilewati']} data sudah ada dan dilewati" : '').'.';

        return back()->with($uji ? 'info' : 'success', $pesan)->with('akun_impor', $h['akun']);
    }
}
