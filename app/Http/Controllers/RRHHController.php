<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RRHHController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return Inertia::render('Cruds/RRHH/Index', ['user' => $user]);
    }
}
