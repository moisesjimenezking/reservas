<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('mesa_ids');
            $table->date('fecha_reserva');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('cantidad_personas');
            $table->enum('estado', ['confirmada', 'cancelada'])->default('confirmada');
            $table->timestamps();

            $table->index(['fecha_reserva', 'estado']);
            // Composite index for dashboard query: date + status + time ordering
            $table->index(['fecha_reserva', 'estado', 'hora_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
