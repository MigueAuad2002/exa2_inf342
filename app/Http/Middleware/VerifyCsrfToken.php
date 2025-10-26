<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        '/login',     // 🔥 ignorar CSRF para login
        '/logout',    // opcional, si luego lo usas
    ];
}
