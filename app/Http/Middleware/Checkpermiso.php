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

/**
 * Middleware class Checkpermiso
 * Uso en rutas: ->middleware('checkpermiso:VER_ROLES') o registrar en kernel.php
 * El parámetro (p.ej. VER_ROLES) es el nombre del permiso esperado.
 */
class Checkpermiso
{
    /**
     * Handle an incoming request.
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $perm
     */
    public function handle($request, Closure $next, $perm = null)
    {
        // Intentamos obtener el id de usuario desde la sesión (ajusta si usas Auth::id())
        $userId = $request->session()->get('user_code');
        if (! $userId) {
            // No autenticado
            if ($request->ajax()) return response()->json(['success'=>false,'message'=>'No autenticado'],401);
            return redirect('/login');
        }

        // Si se pasó un permiso, comprobarlo
        if ($perm && ! user_has_permission($userId, $perm)) {
            if ($request->ajax()) return response()->json(['success'=>false,'message'=>'No autorizado'],403);
            return response()->view('errors.403', [], 403);
        }

        return $next($request);
    }
}
