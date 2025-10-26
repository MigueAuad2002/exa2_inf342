<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Config;

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