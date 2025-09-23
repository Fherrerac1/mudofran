<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->string('numPresupuesto', 50);
            $table->date('fechaInicio');
            $table->date('fechaFin')->nullable();
            $table->integer('estado')->default(0);
            $table->timestamp('firmado')->nullable();
            $table->integer('num_cuenta')->nullable();
            $table->double('total_sin_iva')->default(0);
            $table->double('descuento')->default(0);

            $table->double('total');
            $table->text('observaciones')->nullable();
            $table->text('condiciones')->nullable();
            $table->boolean('anexo')->nullable();
            $table->integer('presupuesto_anexo')->nullable();
            //relations
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->foreignId('contacto_id')->nullable()->constrained('contactos');

            //datos del cliente
            $table->string('cliente_nombre')->nullable();
            $table->string('cliente_dni')->nullable();
            $table->string('cliente_email')->nullable();
            $table->string('cliente_telefono')->nullable();
            $table->string('cliente_localidad')->nullable();
            $table->string('cliente_provincia')->nullable();
            $table->string('cliente_direccion')->nullable();
            $table->string('cliente_cp')->nullable();
            ///////////
            $table->integer('metodo_pago')->nullable();

            $table->double('total_iva')->default(0);
            $table->double('total_irpf')->default(0);
            $table->double('iva')->default(0);
            $table->double('irpf')->default(0);

            $table->json('servicios')->nullable();
            $table->json('productos')->nullable();
            $table->json('fotos')->nullable();

            $table->string('hash')->nullable();
            $table->string('pdf')->nullable();

        });
    }

    public function down()
    {
        Schema::dropIfExists('presupuestos');
    }
};
