<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plantillas_maestras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('tipo');                                        // Tipo como string (facturas)
            $table->string('letra')->nullable();                           // Campo para almacenar una letra
            $table->integer('serie');                                      // Campo para almacenar una serie (1, 2, 7, etc)
            $table->string('year');                                        // Campo para almacenar un año ejemplo (25 o 2025)
            $table->integer('cantidad');                                   // Campo numérico entero
            $table->string('simbolo_1');                                   // Campo para almacenar un simbolo_1 (- o /)
            $table->string('simbolo_2');                                   // Campo para almacenar un simbolo_2 (- o /)
            $table->string('opcional')->nullable();                        // Campo opcional
            $table->boolean('numeroSerieActivo')->default(true);    // Campo para indicar si la serie está activa
            $table->json('orden')->nullable();                             // Campo para almacenar el orden que puede tener la la plantilla

            // Relaciones
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); // Usuario que actualizó el registro

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas_maestras');
    }
};
