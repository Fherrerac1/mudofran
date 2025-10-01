<?php

use App\Http\Middleware\TenantScope;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Foundation\Application;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\AccessLogsController;
use App\Http\Controllers\BorradoresController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\ContactosController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\FacturaRecurrenteController;
use App\Http\Controllers\FacturasController;
use App\Http\Controllers\NominaController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\PlantillaMaestraController;
use App\Http\Controllers\PresupuestosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ServiciosController;
use App\Http\Controllers\TimeOffController;
use App\Http\Controllers\ViewController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;

// IMPORTS DE PRUEBAS
use App\Http\Controllers\PruebasController;


// Landing page & login
Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::post('login', [AuthenticatedSessionController::class, 'store']);

Route::get('login-with-token/{email}/{token}', [AuthenticatedSessionController::class, 'loginWithToken'])
    ->name('loginWithToken');
// Auth routes
Route::middleware(['auth', TenantScope::class])->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [ViewController::class, 'index'])->name('dashboard');

    Route::get('/dashboard/excel', [ViewController::class, 'excel'])->name('excel');
    //profile
    Route::get('my_zone', [ProfileController::class, 'myZone'])->name('my.zone');
    Route::post('/users/{id}/update-password', [ProfileController::class, 'updatePasswordByAdmin'])
        ->name('user.update-password')
        ->middleware(['auth']);

    //metodo de pago routs
    Route::post('/store_metodo', [ProfileController::class, 'store_metodo'])->name('perfil.store_metodo');
    Route::post('/update_metodo/{id}', [ProfileController::class, 'updateMetodo'])->name('perfil.update_metodo');
    Route::get('/metodo/destroy/{id}', [ProfileController::class, 'destroyMetodo'])->name('metodo.destroy');

    //notificaciones routs
    Route::get('/lista_notificaciones', [NotificationsController::class, 'index'])->name('notificaciones.index');
    Route::get('/mis-notificaciones', [NotificationsController::class, 'liveNotificaciones'])->name('notificacion.liveNotificaciones');
    Route::get('/push-notificaciones', [NotificationsController::class, 'pushNotificaciones'])->name('notificacion.pushNotificaciones');
    Route::get('/notificaciones/{id}/delete', [NotificationsController::class, 'destroy'])->name('delete.notificacion');

    Route::get('/home', [ViewController::class, 'index'])->name('index');

    //Clientes
    Route::get('/list_clientes', [ClientesController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/create', [ClientesController::class, 'create'])->name('clientes.create');
    Route::post('/cliente/save', [ClientesController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{id}/mostrar', [ClientesController::class, 'show'])->name('clientes.show');
    Route::get('/clientes/{id}/editar', [ClientesController::class, 'edit'])->name('clientes.edit');
    Route::post('/clientes/{id}/update', [ClientesController::class, 'update'])->name('clientes.update');
    Route::get('/clientes/{id}/delete', [ClientesController::class, 'destroy'])->name('clientes.destroy');
    Route::get('/clientes/descargar-pdf', [ClientesController::class, 'descargarPDF'])->name('clientes.descargarPDF');
    Route::get('/clientes/modelo347PDF/{id}', [ClientesController::class, 'modelo347PDF'])->name('clientes.modelo347PDF');

    //Contactos
    Route::get('/list_contactos', [ContactosController::class, 'index'])->name('contactos.index');
    Route::get('/contactos/create', [ContactosController::class, 'create'])->name('contactos.create');
    Route::post('/contacto/save', [ContactosController::class, 'store'])->name('contactos.store');
    Route::get('/contactos/{id}/editar', [ContactosController::class, 'edit'])->name('contactos.edit');
    Route::post('/contactos/{id}/update', [ContactosController::class, 'update'])->name('contactos.update');
    Route::get('/contactos/{id}/delete', [ContactosController::class, 'destroy'])->name('contactos.destroy');

    //Presupuestos
    Route::get('/list_presupuestos', [PresupuestosController::class, 'index'])->name('presupuestos.index');
    Route::get('/presupuestos/create', [PresupuestosController::class, 'create'])->name('presupuestos.create');
    Route::get('/presupuestos/{id}/mostrar', [PresupuestosController::class, 'show'])->name('presupuestos.show');
    Route::get('/presupuestos/{id}/editar', [PresupuestosController::class, 'edit'])->name('presupuestos.edit');
    Route::post('/presupuestos/{id}/update', [PresupuestosController::class, 'update'])->name('presupuestos.update');
    Route::get('/presupuestos/{id}/delete', [PresupuestosController::class, 'destroy'])->name('presupuestos.destroy');
    Route::post('/presupuestos/create', [PresupuestosController::class, 'store'])->name('presupuestos.store');
    Route::put('/presupuestos/{id}/estado', [PresupuestosController::class, 'estado'])->name('presupuesto.estado');
    Route::post('/change-metodo/{id}/{metodo}', [PresupuestosController::class, 'changeMetodo'])->name('change.metodo');
    Route::get('/presupuestos/crear_anexo/{id}', [PresupuestosController::class, 'createAnexo'])->name('presupuestos.anexo');
    Route::post('/presupuestos/crear_anexo/{id}', [PresupuestosController::class, 'store'])->name('anexo.store');
    Route::get('/presupuestos/{id}/duplicate', [PresupuestosController::class, 'duplicate'])->name('presupuestos.duplicate');
    Route::post('/presupuestos/{id}/send_email', [PresupuestosController::class, 'send_email'])->name('presupuestos.email');
    Route::get('/generate/presupuesto_number', [PresupuestosController::class, 'generatePresupuestoNumber'])->name('generate.presupuesto.number');
    Route::get('/descarga-presupuestos', [PresupuestosController::class, 'reportPresupuestos'])->name('presupuestos.report');
    //Facturas
    Route::get('/list_facturas', [FacturasController::class, 'index'])->name('facturas.index');
    Route::get('/facturas/create', [FacturasController::class, 'create'])->name('facturas.create');
    Route::get('facturas/{id}/mostrar', [FacturasController::class, 'show'])->name('facturas.show');
    Route::get('/facturas/{id}/editar', [FacturasController::class, 'edit'])->name('facturas.edit');
    Route::post('/facturas/{id}', [FacturasController::class, 'update'])->name('facturas.update');
    Route::get('/facturas/{id}/delete', [FacturasController::class, 'destroy'])->name('facturas.destroy');
    Route::get('/facturas/{id}/destroy', [FacturasController::class, 'delete'])->name('facturas.delete');
    Route::post('/facturas_store', [FacturasController::class, 'store'])->name('facturas.store');
    Route::get('/facturas/{id}/generate', [FacturasController::class, 'generate'])->name('facturas.generate');
    Route::get('/facturas/{id}/duplicate', [FacturasController::class, 'duplicate'])->name('facturas.duplicate');
    Route::get('/facturas/{id}/rectificate', [FacturasController::class, 'rectificate'])->name('facturas.rectificate');
    Route::get('/facturas/{id}/generateSerie7', [FacturasController::class, 'generateDeSerie7'])->name('facturas.generateSerie7');
    Route::post('/facturas/{id}/generate', [FacturasController::class, 'store'])->name('facturas.fractionate');
    Route::post('/facturas/{id}/fractionate', [FacturasController::class, 'fraction'])->name('facturas.fraction');
    Route::put('/facturas/{id}/estado', [FacturasController::class, 'estado'])->name('facturas.estado');
    Route::post('/libro-de-facturas', [FacturasController::class, 'libroDeFacturas'])->name('libro-de-facturas');
    Route::get('/generate/{serie}/facturas_number', [FacturasController::class, 'generateFacturaNumber'])->name('generate.factura.number');
    Route::post('/facturas/{id}/send_email', [FacturasController::class, 'send_email'])->name('facturas.email');
    Route::post('/facturas/{id}/add_cobro', [FacturasController::class, 'createCobro'])->name('cobro.store');
    Route::get('/descarga-facturas', [FacturasController::class, 'reportFacturas'])->name('facturas.report');
    Route::post('/factura/download/{facturaHash}/{id}', [FacturasController::class, 'downloadFactura'])->name('facturae.download');

    //Facturas
    Route::get('/lista_recurrentes', [FacturaRecurrenteController::class, 'index'])->name('recurrentes.index');
    Route::get('/facturas-recurrentes-create', [FacturaRecurrenteController::class, 'create'])->name('recurrentes.create');
    Route::post('/facturas-recurrentes-store', [FacturaRecurrenteController::class, 'store'])->name('recurrentes.store');
    Route::post('/facturas-recurrentes-update/{id}', [FacturaRecurrenteController::class, 'update'])->name('recurrentes.update');

    //borradoes
    Route::get('/facturas_borradores', [BorradoresController::class, 'index'])->name('facturas.borradores.index');
    Route::get('/facturas_borrador_create', [BorradoresController::class, 'create'])->name('facturas.borradores.create');
    Route::get('/facturas_borrador_edit/{id}', [BorradoresController::class, 'edit'])->name('facturas.borradores.edit');
    Route::get('facturas_borrador/{id}/mostrar', [BorradoresController::class, 'show'])->name('facturas.borradores.show');
    Route::get('facturas_borrador/{id}/generate', [BorradoresController::class, 'generate'])->name('facturas.borradores.generate');
    Route::get('facturas_borrador_presupuesto/{id}/generate', [BorradoresController::class, 'generate_presupuesto'])->name('facturas_presupuesto.borradores.generate');

    Route::get('/recurrentes/{id}/delete', [FacturaRecurrenteController::class, 'destroy'])->name('recurrentes.destroy');

    //users routs
    Route::get('/list_users', [UserController::class, 'index'])->name('user.index');
    Route::get('/user_create', [UserController::class, 'create'])->name('user.create');
    Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::post('/user/save', [UserController::class, 'store'])->name('user.store');
    Route::post('/user/{id}/update', [UserController::class, 'update'])->name('user.update');
    Route::get('/user/{id}/delete', [UserController::class, 'destroy'])->name('delete.usuario');
    Route::get('/user/{id}/mostrar', [UserController::class, 'show'])->name('user.show');

    //Servicios and Productos
    Route::get('/lista_servicios', [ServiciosController::class, 'index'])->name('servicios.index');
    Route::post('/servicios_store', [ServiciosController::class, 'store'])->name('servicios.store');
    Route::post('/servicios/{id}/update', [ServiciosController::class, 'update'])->name('servicios.update');
    Route::get('/servicios/{id}/mostrar', [ServiciosController::class, 'show'])->name('servicios.show');
    Route::get('/servicios/{id}/delete', [ServiciosController::class, 'destroy'])->name('servicios.destroy');
    Route::get('/descarga-servicios-productos', [ServiciosController::class, 'reportServicios'])->name('servicios.report');

    //productos
    Route::get('/lista_productos', [ProductosController::class, 'index'])->name('productos.index');
    Route::post('/productos_store', [ProductosController::class, 'store'])->name('productos.store');
    Route::post('/productos/{id}/update', [ProductosController::class, 'update'])->name('productos.update');
    Route::get('/productos/{id}/mostrar', [ProductosController::class, 'show'])->name('productos.show');
    Route::get('/productos/{id}/delete', [ProductosController::class, 'destroy'])->name('productos.destroy');
    Route::get('/descarga-productos', [ProductosController::class, 'reportProductos'])->name('productos.report'); // nuevo

    //Observacion actividades
    Route::get('/list/activities', [AccessLogsController::class, 'index'])->name('activities.index');
    Route::get('/delete-history', [AccessLogsController::class, 'destroy'])->name('access-logs.destroy');

    //CONFIG
    Route::get('/configuracion', [ConfigurationController::class, 'index'])->name('config.index');
    Route::post('/configuracion', [ConfigurationController::class, 'update'])->name('config.update');
    Route::get('/pusher_data', [ConfigurationController::class, 'pusher_data'])->name('config.pusher_data');

    //fichajes
    Route::get('/list_horarios', [TimeController::class, 'index'])->name('clock.index');
    Route::post('/fichar', [TimeController::class, 'fichar'])->name('fichar');
    Route::post('/horario/{id}/update', [TimeController::class, 'update'])->name('record.update');
    Route::post('/horario/{id}/approve', [TimeController::class, 'approve'])->name('record.approve');
    Route::get('/horario/{id}/delete', [TimeController::class, 'destroy'])->name('record.delete');
    Route::get('/get_record_status', [TimeController::class, 'getStatus'])->name('record.estado');
    Route::post('/accept_all', [TimeController::class, 'acceptAll'])->name('accept.all.records');
    Route::get('/control-horario', [TimeController::class, 'calcularFichajeHoyUser'])->name('control-horario');
    Route::get('/live-fichaje', [TimeController::class, 'live_fichaje'])->name('live-fichaje');
    Route::get('/descarga-fichajes', [TimeController::class, 'descargaFichajes'])->name('record.report');
    Route::post('/toggle-pause', [TimeController::class, 'togglePause'])->name('toggle.pause');
    Route::post('/toggle-meal', [TimeController::class, 'toggleMeal'])->name('toggle.meal');
    Route::post('/horario/{id}/update-meal', [TimeController::class, 'updateMeal'])->name('record.update-meal');
    Route::post('/horario/{id}/delete-meal', [TimeController::class, 'deleteMeal'])->name('record.delete-meal');
    Route::post('/horario/{id}/finalizarJornada', [TimeController::class, 'finalizarJornada'])->name('record.finalizarJornada');


    Route::post('/observaciones', [TimeController::class, 'guardarObservacion'])->name('observaciones.store');
    Route::get('/observaciones/{id}/mostrar', [TimeController::class, 'mostrarObservacion'])->name('observaciones.mostrar');

    //documentos
    Route::get('/documentos', [DocumentoController::class, 'index'])->name('documentos.index');
    Route::post('/documentos/store', [DocumentoController::class, 'store'])->name('documentos.store');
    Route::get('/documentos/{documento}', [DocumentoController::class, 'destroy'])->name('documentos.destroy');

    //documentos
    Route::get('/nominas', [NominaController::class, 'index'])->name('nominas.index');
    Route::post('/nominas/store', [NominaController::class, 'store'])->name('nominas.store');
    Route::get('/nominas/{nomina}', [NominaController::class, 'destroy'])->name('nominas.destroy');
    Route::get('/nominas/{id}/mostrar', [NominaController::class, 'show'])->name('nomina.show');

    //time_off
    Route::get('/time-off', [TimeOffController::class, 'index'])->name('time_off.index');
    Route::post('/time-off', [TimeOffController::class, 'store'])->name('time_off.store');
    Route::get('/time-off/{id}/mostrar', [TimeOffController::class, 'show'])->name('time_off.show');
    Route::put('/time-off/{id}', [TimeOffController::class, 'update'])->name('time_off.update');
    Route::delete('/time-off/{id}', [TimeOffController::class, 'destroy'])->name('time_off.destroy');

    //servicios completer
    Route::get('/servicios/{id}/data', [ServiciosController::class, 'getServiciosData']);
    Route::get('/servicios/search', [ServiciosController::class, 'getAllServicios']);

    //productos completer
    Route::get('/productos/{id}/data', [ProductosController::class, 'getProductosData']);
    Route::get('/productos/search', [ProductosController::class, 'getAllProductos']);

    // Plantilla Maestrapatch
    Route::patch('perfil/plantilla', [PlantillaMaestraController::class, 'update'])->name('perfil.update_plantilla');
    Route::post('/plantillas-maestras/default', [PlantillaMaestraController::class, 'storeDefault'])->name('plantillas_maestras.store_default');

    //this one is for push notificationes
    Route::patch('/fcm-token', [NotificationsController::class, 'updateToken'])->name('fcmToken');

});

// Superadmin tenant management
Route::middleware(['auth'])->group(function () {
    Route::get('tenants_dashboard', [TenantController::class, 'dashboard'])->name('tenants.dashboard');
    Route::get('tenants_list', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('tenants/create', [TenantController::class, 'create'])->name('tenants.create');
    Route::post('tenants/store', [TenantController::class, 'store'])->name('tenants.store');
    Route::get('tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
    Route::post('tenants/{tenant}/update', [TenantController::class, 'update'])->name('tenants.update');
    Route::delete('tenants/{tenant}/destroy', [TenantController::class, 'destroy'])->name('tenants.destroy');
});


// PRUEBAS PARA LA VENTANA DE IMPORTACIÓN
Route::get('/pruebas', [PruebasController::class, 'index'])->name('pruebas.index');
Route::post('/pruebas/importar', [PruebasController::class, 'importar'])->name('pruebas.importar');

require __DIR__ . '/auth.php';

