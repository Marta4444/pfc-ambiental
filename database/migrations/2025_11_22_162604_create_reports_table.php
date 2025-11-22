<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcategory_id')->constrained()->onDelete('cascade');
            $table->string('ip', 20)->unique();
            $table->string('title');
            $table->text('background');
            $table->string('community', 100); //Comunidad Autónoma
            $table->string('province', 100);
            $table->string('locality', 255);
            $table->foreignId('petitioner_id')->constrained('petitioners')->onDelete('restrict');
            $table->string('petitioner_other', 255)->nullable();
            $table->enum('urgency', ['normal', 'alta', 'urgente'])->default('normal');
            $table->date('date_petition');
            $table->date('date_damage');
            $table->enum('status', ['nuevo', 'en_proceso', 'en_espera', 'completado'])->default('nuevo'); //Al crearse, se asigna el estado Nuevo.
            $table->boolean('assigned')->default(false);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('pdf_report')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};