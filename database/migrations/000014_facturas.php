<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->string('numFactura', 50);
            $table->date('fechaInicio');
            $table->date('fechaFin');
            $table->integer('estado')->default(0);
            $table->integer('serie')->default(1);
            $table->integer('retencion')->default(0);
            $table->double('descuento')->default(0);
            $table->double('iva');
            $table->integer('tiempo');
            $table->string('num_cuenta')->nullable();
            $table->double('total_sin_iva');
            $table->double('total_iva')->nullable();
            $table->double('total_irpf')->nullable();
            $table->double('irpf');
            $table->double('total');
            $table->double('porcentaje')->default(100.00);
            $table->string('concepto', 500)->nullable();
            ///////////
            $table->foreignId('presupuesto_id')->nullable()->constrained('presupuestos');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');

            //datos del cliente
            $table->string('cliente_nombre')->nullable();
            $table->string('cliente_dni')->nullable();
            $table->string('cliente_email')->nullable();
            $table->string('cliente_telefono')->nullable();
            $table->string('cliente_localidad')->nullable();
            $table->string('cliente_provincia')->nullable();
            $table->string('cliente_direccion')->nullable();
            $table->string('cliente_cp')->nullable();

            $table->unsignedBigInteger('metodo_pago')->nullable();
            $table->unsignedBigInteger('factura_nativa')->nullable();

            $table->text('observaciones')->nullable();
            $table->text('condiciones')->nullable();

            $table->string('hash')->nullable();
            $table->longText('qr_code')->nullable();
            $table->text('pdf')->nullable();

            $table->json('servicios')->nullable();
            $table->json('productos')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table');
    }
};
