<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre');
            $table->foreignId('tipo_mascota_id')->constrained('tipo_mascotas');
            $table->string('raza');
            $table->date('fecha_nacimiento');
            $table->text('descripcion');
            $table->string('foto')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};