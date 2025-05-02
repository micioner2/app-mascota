<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tipo_mascotas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->softDeletes();
        });

        DB::table('tipo_mascotas')->insert([
            ['nombre' => 'Perro'],
            ['nombre' => 'Gato'],
            ['nombre' => 'Caballo'],
            ['nombre' => 'Ave'],
            ['nombre' => 'Otro']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_mascotas');
    }
};
