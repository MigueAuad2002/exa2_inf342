<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Config;

//ENDPOINT LOGIN
Route::match(['get','post'],'/login',function(Request $request)
{
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');

    //SI ES UNA SOLICITUD GET REDIRIGIR A TEMPLATE LOGIN
    if ($request->isMethod('get'))
    {
        return view('login');
    }

    //OBTENER DATOS
    $data=$request->json()->all();
    $codigo=$data['codigo'];
    $password=$data['password'];

    if (!$codigo || !$password)
    {
        return response()->json([
            'success'=>false,
            'message'=>'Debe enviar todos los datos'
        ],401);
    }
    try
    {
        $db=Config::$db;
        $db->create_conection();

        //VALIDACION:VERIFICAR SI EL USUARIO CON EL CODIGO INGRESADO EXISTE
        $sql="
            SELECT codigo,password_hash
            FROM ex_g32.usuario
            WHERE codigo= :codigo
        ";
        $params=[
            ':codigo'=>$codigo
        ];
        $stmt=$db->execute_query($sql,$params);
        
        $usuario=$db->fetch_one($stmt);

        if (!$usuario)
        {
            return response()->json([
                'success'=>false,
                'message'=>'El usuario no existe.'
            ],401);    
        }
        //VALIDAR CONTRASEÑA
        if (!password_verify($password,$usuario['password_hash']))
        {
            return response()->json([
                'success'=>false,
                'message'=>'Contraseña incorrecta.'
            ],401);
        }

        //SI LA CONTRASEÑA ES CORRECTA GUARDAR SESION
        Session::put('user_code',$usuario['codigo']);

        return response()->json([
            'success'=>true,
            'message'=>'Inicio de Sesion exitoso.'
        ]);
    }
    catch (Exception $e)
    {
        return response()->json([
            'success'=>false,
            'message'=>'Error al Iniciar Sesion.',
            'error'=>$e->getMessage()
        ],500);
    }
    finally
    {
        if (isset($db) && $db !== null) 
        {
            $db->close_conection();
        }
    }
});

//ENDPOINT LOGOUT
Route::post('/logout',function()
{
    Session::flush();

    return redirect('/login');
});