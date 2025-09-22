<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * Cookies, які НЕ потрібно шифрувати
     *
     * @var array<int, string>
     */
    protected $except = ['_fbp','_fbc','_extid'];
}
