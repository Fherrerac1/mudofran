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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->string('nombre');
            $table->string('apellido_1')->nullable();
            $table->string('apellido_2')->nullable();
            $table->string('email')->unique();
            $table->integer('telefono_mobile');
            $table->integer('telefono_fijo')->nullable();
            $table->string('dni', 9);
            $table->string('direccion');
            $table->string('category')->default('Personal');
            $table->string('localidad');
            $table->string('provincia');
            $table->string('num_cuenta')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('pais')->default('España');
            $table->integer('cp')->nullable();
            $table->boolean('share_data')->default(false);
            $table->string('api_key')->nullable();
            $table->string('dir3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clientes');
    }
};
