<?php

use App\Http\Controllers\APIController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\VerifactuApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/mis_facturas/{api_key}/facturas', [APIController::class, 'clienteFacturas']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
