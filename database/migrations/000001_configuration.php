<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('configuration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('footer_text')->nullable();
            $table->text('style_color')->nullable();
            $table->text('text_color')->nullable();
            $table->text('unique_color')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->text('serial_num')->nullable();
            $table->text('url_app')->nullable();
            $table->text('pfx_certificate')->nullable();
            $table->text('pfx_password')->nullable();
            $table->text('business_name')->nullable();
            $table->text('address')->nullable();
            $table->text('postal_code')->nullable();
            $table->text('phone')->nullable();
            $table->text('town')->nullable();
            $table->text('province')->nullable();
            $table->text('email')->nullable();
            $table->text('main_logo')->nullable();
            $table->text('tax_id')->nullable();
            $table->boolean('tecnicos')->default(0);
            $table->boolean('series')->default(0);
            $table->json('series_types')->default(json_encode([1]));
            $table->text('business_type')->nullable(); // 'individual' or 'corporate'
            $table->text('color')->default('#ffffff');
            $table->integer('factura_mode')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('configuration');
    }
};
