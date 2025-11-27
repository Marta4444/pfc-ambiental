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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcategory_id')->constrained()->onDelete('cascade');
            $table->string('ip', 20)->unique();
            $table->string('title');
            $table->text('background');
            $table->string('community', 100); //Comunidad Autónoma
            $table->string('province', 100);
            $table->string('locality', 255);
            $table->string('coordinates', 100)->nullable(); // Coordenadas GPS
            $table->foreignId('petitioner_id')->constrained('petitioners')->onDelete('restrict');
            $table->string('petitioner_other', 255)->nullable();
            $table->string('office', 255)->nullable(); // Despacho/Oficina
            $table->string('diligency', 100)->nullable(); // Diligencias
            $table->enum('urgency', ['normal', 'alta', 'urgente'])->default('normal');
            $table->date('date_petition');
            $table->date('date_damage');
            $table->enum('status', ['nuevo', 'en_proceso', 'en_espera', 'completado'])->default('nuevo'); //Al crearse, se asigna el estado Nuevo.
            $table->boolean('assigned')->default(false);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('pdf_report')->nullable();
            $table->decimal('vr_total', 10, 2)->nullable(); // VR Total
            $table->decimal('ve_total', 10, 2)->nullable(); // VE Total
            $table->decimal('vs_total', 10, 2)->nullable(); // VS Total
            $table->decimal('total_cost', 10, 2)->nullable(); // Coste Total (genealmente se suman VR, VE y VS totales)
            
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};