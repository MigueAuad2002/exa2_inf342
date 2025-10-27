<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

function user_permissions(int $userId) : array {
    $cacheKey = "user_perms_{$userId}";
    return Cache::remember($cacheKey, 60, function() use ($userId) {
        return DB::table('ex_g32.usuario as u')
            ->leftJoin('ex_g32.rol as r','u.id_rol','r.id')
            ->leftJoin('ex_g32.rol_permiso as rp','rp.id_rol','r.id')
            ->leftJoin('ex_g32.permisos as p','rp.id_permiso','p.id')
            ->where('u.id', $userId)
            ->pluck('p.nombre')
            ->filter() // elimina nulls
            ->map(function($n){ return strtoupper($n); })
            ->toArray();
    });
}

function user_has_permission($userId, $permName) : bool {
    $perms = user_permissions($userId);
    return in_array(strtoupper($permName), $perms);
}
