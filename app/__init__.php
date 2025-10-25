<?php

//CLASE APLICATION DE LARAVEL
use Illuminate\Foundation\Application;

//FUNCION ENCARGADA DE CREAR LA APP [LARAVEL]
function create_app(): Application
{

    $app = require __DIR__ . '/../bootstrap/app.php'; // <--> app=Flask(__init__)

    
    return $app;
}
