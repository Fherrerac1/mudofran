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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable()->unique();
            $table->string('password');
            $table->string('rol')->default('user');
            $table->string('position')->nullable();
            $table->string('telefono')->nullable();
            $table->string('num_seguridad')->nullable();
            $table->date('fecha_alta')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('img')->nullable();
            $table->string('localidad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('direccion')->nullable();
            $table->integer('cp')->nullable();
            $table->double('coste_hora')->default(0);
            $table->string('dni')->nullable()->unique();
            $table->decimal('horario_semanal', 5, 2)->default(40.00);
            $table->integer('dias_laborables')->default(5);
            $table->boolean('blocked')->default(false);
            $table->string('fcm_token')->nullable();
            $table->rememberToken();

            $table->foreignId('tenant_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
