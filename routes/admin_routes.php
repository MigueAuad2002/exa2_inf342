<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Config;
use App\Classes\Postgres_DB;

require_once app_path('/services/help_functs.php');

//ENDPOINT GESTOR DE USUARIOS: ELIMINAR
Route::post('/admin/delete',function(Request $request)
{
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');

    //VALIDACION:USUARIO EN SESION
    if (!Session::has('user_code'))
    {
        return response()->json([
            'success'=>false,
            'message'=>'Usuario no Autenticado.'
        ]);
    }

    //VALIDACION:USUARIO ADMIN
    if (Session::get('user_role')!=='admin')
    {
        return response()->json([
            'success'=>false,
            'message'=>'El Usuario no es Administrador.'
        ]);
    }

    //OBTENER DATOS
    $data=$request->json()->all();
    $codigo_eliminar=$data['id'];

    //OBTENER DATOS BITACORA
    $accion = 'ELIMINAR USUARIO';
    $fecha = date('Y-m-d H:i:s');
    $estado = 'ERROR';
    $comentario = 'Eliminar un usuario indicado.';
    $codigo = Session::get('user_code');

    $db=Config::$db;
    try
    {
        $db->create_conection();

        $sql="  SELECT ci
                FROM ex_g32.usuario
                WHERE codigo= :codigo";
        $params=[':codigo'=>$codigo_eliminar];

        $stmt=$db->execute_query($sql,$params);
        $ci=$db->fetch_one($stmt);

        if ($ci==null)
        {
            $db->save_log_bitacora($accion, $fecha, $estado, $comentario, $codigo);
            return response()->json([
                'success'=>false,
                'message'=>'El usuario no esta Registrado en el Sistema.'
            ]);
        }
        $sql="  DELETE FROM ex_g32.persona
                WHERE ci= :ci";
        $params=[':ci'=>$ci['ci']];
        $stmt=$db->execute_query($sql,$params);

        $estado='SUCCESS';
        $db->save_log_bitacora($accion, $fecha, $estado, $comentario, $codigo);
        return response()->json([
            'success'=>true,
            'message'=>'Usuario eliminado Exitosamente'
        ]);
    }
    catch (Exception $e)
    {
        return response()->json([
            'success'=>false,
            'message'=>'Ocurrio un error en el proceso.',
            'error'=>$e->getMessage()
        ],500);
    }
    finally
    {
        if (isset($db) && $db!==null)
        {
            $db->close_conection();
        }
    }
});
//ENDPOINT GESTION DE USUARIO
Route::get('/admin/users',function()
{
    //VALIDACION: USUARIO EN SESION
    if (!Session::has('user_code'))
    {
        return redirect('/login');
    }

    //VALIDACION: USUARIO ADMIN
    if (Session::get('user_role') != 'admin')
    {
        return redirect('/');
    }

    $db=Config::$db;
    try
    {
        $db->create_conection();

        //REGISTRO DE BITACORA
        $accion = 'GESTION DE USUARIOS';
        $fecha = date('Y-m-d H:i:s');
        $estado = 'ERROR';
        $comentario = 'Consultar Usuarios Registrados.';
        $codigo = Session::get('user_code');

        //OBTENER BITACORA
        $sql="  SELECT u.codigo,u.ci,p.nomb_comp,p.tel,p.correo,r.nombre as rol
                FROM ex_g32.usuario u
                INNER JOIN ex_g32.persona p ON p.ci=u.ci
                INNER JOIN ex_g32.rol r ON r.id =u.id_rol";
        $stmt=$db->execute_query($sql);
        $usuarios=$db->fetch_all($stmt);

        $estado='SUCCESS';
        $db->save_log_bitacora($accion, $fecha, $estado, $comentario, $codigo);

        //RECUPERAR DATOS DEL USUARIO
        $user = [
            'nomb_comp' => Session::get('name'),  // Asegúrate de tener este dato en la sesión
            'rol' => Session::get('user_role'),
            'ci' => Session::get('ci'),
            'correo' => Session::get('mail'),
            'tel' => Session::get('tel'),
        ];

        return view('admin_users',['usuarios' => $usuarios,'user'=>$user]);
    }
    catch(Exception $e)
    {
        return redirect('/admin')->with('error', 'Error al consultar usuarios: ' . $e->getMessage());
    }
    finally
    {
        if (isset($db) && $db!==null)
        {
            $db->close_conection();
        }
    }
});

//RUTA ENCARGADA DE LA BITACORA
Route::get('/admin/bitacora',function()
{
    //VALIDACION: USUARIO EN SESION
    if (!Session::has('user_code'))
    {
        return redirect('/login');
    }

    //VALIDACION: USUARIO ADMIN
    if (Session::get('user_role') != 'admin')
    {
        return redirect('/');
    }

    $db=Config::$db;
    try
    {
        $db->create_conection();

        //REGISTRO DE BITACORA
        $accion = 'CONSULTAR BITACORA';
        $fecha = date('Y-m-d H:i:s');
        $estado = 'ERROR';
        $comentario = 'Consultar Historial de acciones.';
        $codigo = Session::get('user_code');

        //OBTENER BITACORA
        $sql="  SELECT *
                FROM ex_g32.bitacora
                ORDER BY fecha_hora DESC
                LIMIT 30";
        $stmt=$db->execute_query($sql);
        $bitacora=$db->fetch_all($stmt);

        $estado='SUCCESS';
        $db->save_log_bitacora($accion, $fecha, $estado, $comentario, $codigo);

        //RECUPERAR DATOS DEL USUARIO
        $user = [
            'nomb_comp' => Session::get('name'),  // Asegúrate de tener este dato en la sesión
            'rol' => Session::get('user_role'),
            'ci' => Session::get('ci'),
            'correo' => Session::get('mail'),
            'tel' => Session::get('tel'),
        ];

        return view('admin_bitacora',['bitacora' => $bitacora,'user'=>$user]);
    }
    catch(Exception $e)
    {
        return redirect('/admin')->with('error', 'Error al consultar la bitácora: ' . $e->getMessage());
    }
    finally
    {
        if (isset($db) && $db!==null)
        {
            $db->close_conection();
        }
    }
});

//RUTA GESTORA DEL MODULO DE ADMINISTRADORES
Route::get('/admin/mod-adm', function() {
    // VALIDACION: USUARIO EN SESION
    if (!Session::has('user_code')) {
        return redirect('/login');
    }

    // VALIDACION: USUARIO ADMIN
    if (Session::get('user_role') != 'admin') {
        return redirect('/');
    }

    try 
    {
        $db = Config::$db;
        $db->create_conection(); // Crea la conexión

        //CONTAR USUARIOS
        $sql = "SELECT COUNT(*) as cant_user FROM ex_g32.usuario";
        $stmt = $db->execute_query($sql);
        $cant_usuarios = $db->fetch_one($stmt);

        //CONTAR DOCENTES
        $sql = "SELECT COUNT(U.codigo) as cant_docent
                FROM ex_g32.usuario u
                INNER JOIN ex_g32.rol r ON r.id = u.id_rol 
                WHERE r.nombre = 'docente'";
        $stmt = $db->execute_query($sql);
        $cant_docente = $db->fetch_one($stmt);

        //SELECCIONAR GESTION ACTUAL
        $sql="  SELECT nombre
                FROM ex_g32.gestion  
                WHERE CURRENT_DATE BETWEEN fecha_i AND fecha_f;";
        $stmt=$db->execute_query($sql);
        $gestion_actual=$db->fetch_one($stmt);

        //REGISTRO DE BITACORA
        $accion = 'ACCESO A MÓDULO ADMIN';
        $fecha = date('Y-m-d H:i:s');
        $estado = 'SUCCESS';
        $comentario = 'Acceso al módulo de administración.';
        $codigo = Session::get('user_code');
        $db->save_log_bitacora($accion, $fecha, $estado, $comentario, $codigo);

        //RECUPERAR DATOS DEL USUARIO
        $user = [
            'nomb_comp' => Session::get('name'),  // Asegúrate de tener este dato en la sesión
            'rol' => Session::get('user_role'),
            'ci' => Session::get('ci'),
            'correo' => Session::get('mail'),
            'tel' => Session::get('tel'),
        ];
        //ENVIAR RESULTADOS A LA VISTA
        return view('mod_admin', [
            'cant_usuarios' => $cant_usuarios['cant_user'],
            'cant_docente' => $cant_docente['cant_docent'], 
            'gestion_actual' => $gestion_actual['nombre'],
            'user'=>$user,
        ]);
    } 
    catch (Exception $e) 
    {
        // Manejo de excepciones: Si la conexión falla, redirige al login
        return redirect('/')->with('error', 'Error al conectar con la base de datos: ' . $e->getMessage());
    }
    finally
    {
        if (isset($db) && $db!== null)
        {
            $db->close_conection();
        }
    }
});


//RUTA GESTORA DEL MODULO DE IMPORTACION DE USUARIOS
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

    //REGISTRAR BITACORA
    $accion = 'IMPORTAR USUARIOS';
    $fecha = date('Y-m-d H:i:s');
    $estado = 'ERROR';
    $comentario = 'Inicio del proceso de importación de usuarios.';
    $codigo = Session::get('user_code');
    $db = Config::$db;

    //PROCESAR EL ARCHIVO
    try
    {
        
        $result=importar_usuarios($file,$extension);
        
        $db->create_conection();

        $estado='SUCCESS';
        $db->save_log_bitacora($accion, $fecha, $estado, $comentario, $codigo);
        return response()->json([
            'success'=>true,
            'message'=>'Usuarios cargados exitosamente',
            'data'=>$result
        ]);
    }
    catch (\Exception $e)
    {
        $estado = 'ERROR';
        $comentario = 'Error durante la carga del archivo: ' . $e->getMessage();
        $db->save_log_bitacora($accion, $fecha, $estado, $comentario, $codigo);
        return response()->json([
            'success'=>false,
            'message'=>'Ocurrio un error al cargar los usuarios.',
            'error'=>$e->getMessage()
        ]);
    }
    finally
    {
        if (isset($db) && $db!==null)
        {
            $db->close_conection();
        }
    }

});