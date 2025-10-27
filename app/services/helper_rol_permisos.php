<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

function user_permissions(int $userId) : array {
    $cacheKey = "user_perms_{$userId}";
    return Cache::remember($cacheKey, 60, function() use ($userId) {
        return DB::table('ex_g32.user_role as ur')
            ->join('ex_g32.rol as r','ur.id_rol','r.id')
            ->join('ex_g32.rol_permiso as rp','rp.id_rol','r.id')
            ->join('ex_g32.permisos as p','rp.id_permiso','p.id')
            ->where('ur.user_id', $userId)
            ->pluck('p.nombre')
            ->map(function($n){ return strtoupper($n); })
            ->toArray();
    });
}

function user_has_permission($userId, $permName) : bool {
    $perms = user_permissions($userId);
    return in_array(strtoupper($permName), $perms);
}
