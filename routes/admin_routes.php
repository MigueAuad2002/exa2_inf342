<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Config;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


require_once app_path('/services/help_functs.php');

//ENDPOINT LOGOUT
/*Route::get('/admin/import-users',function()
{

    return view('/import_user');
});*/

Route::match(['get','post'],'/admin/import-users',function(Request $request)
{
    //EVITAR ERRORES CORS
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');

    if (!Session::has('user_code'))
    {
        return redirect('/login');
    }

    if ($request->isMethod('get'))
    {
        return view('import_user');
    }

    if (!$request->hasFile('archivo'))
    {
        return response()->json([
            'success'=>false,
            'message'=>'No se ha enviado ningun archivo.'
        ],400);
    }

    $file=$request->file('archivo');

    //VERIFICAMOS EL TIPO DE ARCHIVO (.xlsx/.csv)
    $allowed_extensions=['csv','xlsx'];
    $extension=$file->getClientOriginalExtension();

    if (!in_array($extension,$allowed_extensions))
    {
        return response()->json([
            'success' => false,
            'message' => 'Formato de archivo no válido. Solo se permiten archivos .csv o .xlsx.'
        ], 400);
    }

    //PROCESAR EL ARCHIVO
    try
    {
        $result=importar_usuarios($file,$extension);
         //VALIDAR SI HUBO ERROR EN LA FUNCION (importar_usuarios)
        

        return response()->json([
            'success'=>true,
            'message'=>'Usuarios cargados exitosamente',
            'data'=>$result
        ]);
    }
    catch (\Exception $e)
    {
        return response()->json([
            'success'=>false,
            'message'=>'Ocurrio un error al cargar los usuarios.',
            'error'=>$e->getMessage()
        ]);
    }
});


/*
|--------------------------------------------------------------------------
| Rutas CRUD para PERMISSIONS y ROLES (todo en routes/web.php)
|--------------------------------------------------------------------------
|
| - Uso: AJAX desde el frontend (las vistas blade que ya creaste consumirán estas rutas).
| - Autenticación simple: se verifica Session::has('user_code') (igual que import-users).
| - Respuestas en JSON para que el JS las consuma fácilmente.
|
*/

/**
 * ---------- PERMISSIONS ----------
 * Endpoints:
 *  - GET  /admin/permissions         -> lista permisos (JSON)
 *  - GET  /admin/permissions/{id}    -> detalle permiso (JSON)
 *  - POST /admin/permissions         -> crear permiso
 *  - PUT  /admin/permissions/{id}    -> actualizar permiso
 *  - DELETE /admin/permissions/{id}  -> eliminar permiso
 *
 * Nota: las vistas (si quieres una UI) deberían estar en resources/views/admin/permissions.blade.php
 * y enviar/recibir JSON a estas rutas.
 */

Route::group([], function() {

    // Listar permisos (JSON)
    Route::get('/admin/permissions', function(Request $request) {
        // comprobación de sesión (igual que import-users)
        if (! Session::has('user_code')) {
            return redirect('/login');
        }

        // Si la petición NO es AJAX, devolver la vista Blade para navegación en navegador
        if (! $request->ajax()) {
            // usa la plantilla en app/templates/admin/permisos.blade.php
            return view('admin.permisos');
        }

        // Ajustado a la estructura: tabla `permisos` con columnas `id`, `nombre`, `descripcion`
    $perms = DB::table('ex_g32.permisos')->orderBy('id')->get();
    return response()->json(['permissions' => $perms]);
    });

    // Ver un permiso
    Route::get('/admin/permissions/{id}', function(Request $request, $id) {
        if (! Session::has('user_code')) return redirect('/login');

    $perm = DB::table('ex_g32.permisos')->where('id', $id)->first();
        if (! $perm) return response()->json(['success'=>false,'message'=>'Permiso no encontrado'],404);

        return response()->json(['permission' => $perm]);
    });

    // Crear permiso
    Route::post('/admin/permissions', function(Request $request) {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        if (! Session::has('user_code')) return redirect('/login');

        // Validación usando los nombres esperados: nombre, descripcion
        $v = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255'
        ]);
        if ($v->fails()) {
            return response()->json(['success'=>false,'message'=>$v->errors()->first()],422);
        }
        $nombre = strtoupper($request->input('nombre'));
        $descripcion = $request->input('descripcion');

        // evitar duplicados (case-insensitive) sobre la columna `nombre`
    $exists = DB::table('ex_g32.permisos')->whereRaw('upper(nombre) = ?', [$nombre])->first();
        if ($exists) {
            return response()->json(['success'=>false,'message'=>'El permiso ya existe.'],409);
        }

        $id = DB::table('ex_g32.permisos')->insertGetId([
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);

        $perm = DB::table('ex_g32.permisos')->where('id', $id)->first();
        return response()->json(['success'=>true,'message'=>'Permiso creado.','permission'=>$perm]);
    });

    // Actualizar permiso
    Route::put('/admin/permissions/{id}', function(Request $request, $id) {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        if (! Session::has('user_code')) return redirect('/login');

    $perm = DB::table('ex_g32.permisos')->where('id', $id)->first();
        if (! $perm) return response()->json(['success'=>false,'message'=>'Permiso no encontrado'],404);

        $v = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255'
        ]);
        if ($v->fails()) {
            return response()->json(['success'=>false,'message'=>$v->errors()->first()],422);
        }
        $nombre = strtoupper($request->input('nombre'));
        $descripcion = $request->input('descripcion');

        // comprobar duplicado en otro id (columna nombre)
    $dup = DB::table('ex_g32.permisos')->whereRaw('upper(nombre) = ?', [$nombre])->where('id','!=',$id)->first();
        if ($dup) {
            return response()->json(['success'=>false,'message'=>'El nombre ya existe en otro permiso.'],409);
        }

        DB::table('ex_g32.permisos')->where('id',$id)->update([
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);

        $perm = DB::table('ex_g32.permisos')->where('id', $id)->first();
        return response()->json(['success'=>true,'message'=>'Permiso actualizado.','permission'=>$perm]);
    });

    // Eliminar permiso
    Route::delete('/admin/permissions/{id}', function(Request $request, $id) {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        if (! Session::has('user_code')) return redirect('/login');

    $perm = DB::table('ex_g32.permisos')->where('id', $id)->first();
        if (! $perm) return response()->json(['success'=>false,'message'=>'Permiso no encontrado'],404);

        // proteger permisos críticos (ajusta la lista según necesites)
        $protected = ['VER_PERMISOS', 'VER_ROLES'];
        if (in_array(strtoupper($perm->nombre), $protected)) {
            return response()->json(['success'=>false,'message'=>'Permiso protegido, no se puede eliminar.'],403);
        }

        DB::transaction(function() use ($id) {
            // tabla relacional `rol_permiso` usa columnas id_rol, id_permiso
            DB::table('ex_g32.rol_permiso')->where('id_permiso',$id)->delete();
            DB::table('ex_g32.permisos')->where('id',$id)->delete();
        });

        return response()->json(['success'=>true,'message'=>'Permiso eliminado.']);
    });

    /**
     * ---------- ROLES ----------
     * Endpoints:
     *  - GET  /admin/roles
     *  - GET  /admin/roles/{id}
     *  - POST /admin/roles
     *  - PUT  /admin/roles/{id}
     *  - DELETE /admin/roles/{id}
     */


    // Listar roles con sus permisos
    Route::get('/admin/roles', function(Request $request) {
        if (! Session::has('user_code')) return redirect('/login');

        // Si la petición NO es AJAX, devolver la vista Blade para navegación en navegador
        if (! $request->ajax()) {
            // espera que exista resources/views/admin/roles.blade.php
            return view('admin.roles');
        }

        // Usar la tabla `rol` (id, nombre, descripcion)
    $roles = DB::table('ex_g32.rol')->orderBy('nombre')->get();

        // mapear cada rol con sus permisos (array)
        $rolesWithPerms = $roles->map(function($r) {
            $perms = DB::table('ex_g32.rol_permiso')
                        ->join('ex_g32.permisos','ex_g32.rol_permiso.id_permiso','=','ex_g32.permisos.id')
                        ->where('ex_g32.rol_permiso.id_rol',$r->id)
                        ->select('ex_g32.permisos.id','ex_g32.permisos.nombre','ex_g32.permisos.descripcion')
                        ->get();
            return [
                'id' => $r->id,
                'nombre' => $r->nombre,
                'descripcion' => $r->descripcion ?? null,
                'permissions' => $perms
            ];
        });

        return response()->json(['roles' => $rolesWithPerms]);
    });

    // Obtener rol por id
    Route::get('/admin/roles/{id}', function(Request $request, $id) {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        if (! Session::has('user_code')) return redirect('/login');

    $role = DB::table('ex_g32.rol')->where('id',$id)->first();
        if (! $role) return response()->json(['success'=>false,'message'=>'Rol no encontrado'],404);

    $perms = DB::table('ex_g32.rol_permiso')
            ->join('ex_g32.permisos','ex_g32.rol_permiso.id_permiso','=','ex_g32.permisos.id')
            ->where('ex_g32.rol_permiso.id_rol',$id)
            ->select('ex_g32.permisos.id','ex_g32.permisos.nombre','ex_g32.permisos.descripcion')
            ->get();

        return response()->json(['role' => ['id'=>$role->id,'nombre'=>$role->nombre,'descripcion'=>$role->descripcion ?? null,'permissions'=>$perms]]);
    });

    // Crear rol (con permisos)
    Route::post('/admin/roles', function(Request $request) {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        if (! Session::has('user_code')) return redirect('/login');

        $v = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer'
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'message'=>$v->errors()->first()],422);

        $nombre = strtoupper($request->input('nombre'));
        $descripcion = $request->input('descripcion');
        $permIds = $request->input('permissions', []);

        // Validar que los permisos enviados existen en el esquema ex_g32 (evita usar Rule::exists con schema-qualified name)
        if (!empty($permIds)) {
            $found = DB::table('ex_g32.permisos')->whereIn('id', array_map('intval', $permIds))->pluck('id')->toArray();
            $unique = array_values(array_unique(array_map('intval', $permIds)));
            if (count($found) !== count($unique)) {
                return response()->json(['success'=>false,'message'=>'Alguno de los permisos no existe.'],422);
            }
        }

        // prevenir rol duplicado
    $exists = DB::table('ex_g32.rol')->whereRaw('upper(nombre) = ?', [$nombre])->first();
        if ($exists) return response()->json(['success'=>false,'message'=>'El rol ya existe.'],409);

        try {
            DB::beginTransaction();
            $roleId = DB::table('ex_g32.rol')->insertGetId(['nombre' => $nombre, 'descripcion' => $descripcion]);

            if (!empty($permIds)) {
                $inserts = [];
                foreach ($permIds as $pid) {
                    $inserts[] = ['id_rol'=>$roleId,'id_permiso'=>(int)$pid];
                }
                if (!empty($inserts)) DB::table('ex_g32.rol_permiso')->insert($inserts);
            }

            DB::commit();

            $role = DB::table('ex_g32.rol')->where('id',$roleId)->first();
            $perms = DB::table('ex_g32.rol_permiso')
                        ->join('ex_g32.permisos','ex_g32.rol_permiso.id_permiso','=','ex_g32.permisos.id')
                        ->where('ex_g32.rol_permiso.id_rol',$roleId)
                        ->select('ex_g32.permisos.id','ex_g32.permisos.nombre','ex_g32.permisos.descripcion')
                        ->get();

            return response()->json(['success'=>true,'message'=>'Rol creado.','role'=>['id'=>$role->id,'nombre'=>$role->nombre,'descripcion'=>$role->descripcion ?? null,'permissions'=>$perms]]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>'Error al crear rol: '.$e->getMessage()],500);
        }
    });

    // Actualizar rol
    Route::put('/admin/roles/{id}', function(Request $request, $id) {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        if (! Session::has('user_code')) return redirect('/login');

    $role = DB::table('ex_g32.rol')->where('id',$id)->first();
        if (! $role) return response()->json(['success'=>false,'message'=>'Rol no encontrado'],404);

        $v = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer'
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'message'=>$v->errors()->first()],422);

        $nombre = strtoupper($request->input('nombre'));
        $descripcion = $request->input('descripcion');
        $permIds = $request->input('permissions', []);

        // Validar que los permisos enviados existen en el esquema ex_g32 (evita usar Rule::exists con schema-qualified name)
        if (!empty($permIds)) {
            $found = DB::table('ex_g32.permisos')->whereIn('id', array_map('intval', $permIds))->pluck('id')->toArray();
            $unique = array_values(array_unique(array_map('intval', $permIds)));
            if (count($found) !== count($unique)) {
                return response()->json(['success'=>false,'message'=>'Alguno de los permisos no existe.'],422);
            }
        }

        // check duplicate nombre
    $dup = DB::table('ex_g32.rol')->whereRaw('upper(nombre) = ?', [$nombre])->where('id','!=',$id)->first();
        if ($dup) return response()->json(['success'=>false,'message'=>'Ya existe otro rol con ese nombre.'],409);

        try {
            DB::beginTransaction();
            DB::table('ex_g32.rol')->where('id',$id)->update(['nombre'=>$nombre,'descripcion'=>$descripcion]);

            // sincronizar permisos: borrar y volver a insertar en rol_permiso
            DB::table('ex_g32.rol_permiso')->where('id_rol',$id)->delete();
            if (!empty($permIds)) {
                $inserts = [];
                foreach ($permIds as $pid) $inserts[] = ['id_rol'=>$id,'id_permiso'=>$pid];
                if (!empty($inserts)) DB::table('ex_g32.rol_permiso')->insert($inserts);
            }

            DB::commit();

            $perms = DB::table('ex_g32.rol_permiso')
                        ->join('ex_g32.permisos','ex_g32.rol_permiso.id_permiso','=','ex_g32.permisos.id')
                        ->where('ex_g32.rol_permiso.id_rol',$id)
                        ->select('ex_g32.permisos.id','ex_g32.permisos.nombre','ex_g32.permisos.descripcion')
                        ->get();

            return response()->json(['success'=>true,'message'=>'Rol actualizado.','role'=>['id'=>$id,'nombre'=>$nombre,'descripcion'=>$descripcion ?? null,'permissions'=>$perms]]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>'Error al actualizar rol: '.$e->getMessage()],500);
        }
    });

    // Eliminar rol
    Route::delete('/admin/roles/{id}', function(Request $request, $id) {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        if (! Session::has('user_code')) return redirect('/login');

    $role = DB::table('ex_g32.rol')->where('id',$id)->first();
        if (! $role) return response()->json(['success'=>false,'message'=>'Rol no encontrado'],404);

        // proteger rol especial
        if (strtoupper($role->nombre) === 'SUPER_ADMIN') {
            return response()->json(['success'=>false,'message'=>'No se puede eliminar SUPER_ADMIN'],403);
        }

        try {
            DB::beginTransaction();
            DB::table('ex_g32.rol_permiso')->where('id_rol',$id)->delete();
            // no se borra user_role aquí porque el esquema de usuarios puede variar
            DB::table('ex_g32.rol')->where('id',$id)->delete();
            DB::commit();

            return response()->json(['success'=>true,'message'=>'Rol eliminado.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>'Error al eliminar rol: '.$e->getMessage()],500);
        }
    });

}); // fin group
