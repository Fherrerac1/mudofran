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
        Schema::create('time_records', function (Blueprint $table) {
            $table->id(); // Crea un ID autoincremental
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id'); // ID del usuario

            $table->time('time_in')->nullable(); // Hora de entrada
            $table->decimal('latitude', 10, 8)->nullable(); // Latitud GPS entrada
            $table->decimal('longitude', 11, 8)->nullable(); // Longitud GPS entrada

            $table->time('time_out')->nullable(); // Hora de salida
            $table->decimal('latitude_out', 10, 8)->nullable(); // Latitud GPS salida
            $table->decimal('longitude_out', 11, 8)->nullable(); // Longitud GPS salida

            $table->integer('estado')->default(0); // Estado del registro

            $table->timestamp('pause_started_at')->nullable(); // Fecha y hora de inicio de pausa
            $table->unsignedInteger('pause_total_seconds')->default(0);

            $table->timestamp('meal_started_at')->nullable(); // Fecha y hora de inicio de comida
            $table->unsignedInteger('meal_total_seconds')->default(0); // Duración total de comida

            $table->json('observaciones')->nullable(); // Campo para guardar JSON de observaciones


            $table->timestamps(); // Campos created_at y updated_at

            // Clave foránea para user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_records');
    }
};
