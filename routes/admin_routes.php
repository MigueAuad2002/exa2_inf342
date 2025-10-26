<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Config;

//ENDPOINT LOGOUT
Route::get('/admin/import-users',function()
{

    return view('/import_user');
});