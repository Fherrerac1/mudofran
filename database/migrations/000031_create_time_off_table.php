<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeOffTable extends Migration
{
    public function up()
    {
        Schema::create('time_off', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->date('from');
            $table->date('to');
            $table->tinyInteger('estado')->default(0);
            $table->unsignedBigInteger('accepted_by')->nullable();
            $table->timestamps();

            // Foreign keys (optional if you want constraints)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('accepted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('time_off');
    }
}
