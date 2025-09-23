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
        Schema::create('facturas_recurrentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('factura_id')->constrained('facturas')->onDelete('cascade');
            $table->enum('frecuencia', ['diaria', 'semanal', 'mensual', 'trimestral', 'semestral', 'anual']);
            $table->date('fechaInicio'); // When the recurrence starts
            $table->date('fechaFin')->nullable(); // Optional: When it ends
            $table->integer('repeticiones')->nullable(); // Null = Infinite recurrence
            $table->integer('repeticiones_actuales')->default(0);
            $table->date('proxima_fecha'); // New column for the next recurrence date
            $table->timestamps();

            // Add a unique constraint to the factura_id column to ensure uniqueness
            $table->unique('factura_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas_recurrentes');
    }
};
