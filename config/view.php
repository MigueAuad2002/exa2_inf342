<?php

return [

    //DIRECTORIO DONDE SE ENCUENTRAN LOS TEMPLATES (HTML)
    'paths' => [
        base_path('app/templates'),
    ],

    //DIRECTORIO PARA EL CACHE DE LAS VISTAS (TEMPLATES)
    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
