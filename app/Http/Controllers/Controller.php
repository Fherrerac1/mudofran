<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public function admin_rol()
    {
        $user = Auth::user();
        if ($user->rol !== 'admin' && $user->rol !== 'gestor' && $user->rol !== 'super' && $user->rol !== 'secretario') {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
        }
    }
}
