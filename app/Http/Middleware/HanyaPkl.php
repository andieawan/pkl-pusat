<?php

namespace App\Http\Middleware;

use App\Services\Akses;
use Closure;
use Illuminate\Http\Request;

/** Batas akses: "pengelola" (admin/koordinator), "pembimbing" (guru/industri/pengelola), "siswa", "admin". */
class HanyaPkl
{
    public function handle(Request $request, Closure $next, string $level)
    {
        $u = $request->user();
        $ok = $u && match ($level) {
            'admin' => $u->isAdmin(),
            'pengelola' => $u->isPengelola(),
            'pembimbing' => Akses::pembimbing($u),
            'siswa' => $u->hasRole('siswa') && $u->siswa() !== null,
            default => false,
        };
        abort_unless($ok, 403, 'Anda tidak memiliki akses ke bagian ini.');

        return $next($request);
    }
}
