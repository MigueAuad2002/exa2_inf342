<?php
use Illuminate\Support\Facades\DB;

function user_permissions(int $userId) : array {
    // Un usuario tiene un solo rol, por lo que obtenemos primero su rol
    $userRole = DB::table('ex_g32.usuario')
        ->where('codigo', $userId)
        ->value('id_rol');

        if (!$userRole) {
            return [];
        }

        // Obtenemos todos los permisos asociados a ese rol
        return DB::table('ex_g32.rol_permiso as rp')
            ->join('ex_g32.permisos as p', 'rp.id_permiso', '=', 'p.id')
            ->where('rp.id_rol', $userRole)
            ->pluck('p.nombre')
            ->map(function($n) { 
                return strtoupper($n); 
            })
            ->toArray();
}

function user_has_permission($userId, $permName) : bool {
    if (empty($userId) || empty($permName)) {
        return false;
    }
    $perms = user_permissions($userId);
    return in_array(strtoupper($permName), $perms);
}
