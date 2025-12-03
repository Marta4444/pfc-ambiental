<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_details', function (Blueprint $table) {
            $table->id();
            
            // Relación con Report
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            
            // Agrupación de campos relacionados (misma especie, mismo residuo, etc.)
            // Formato: "species_1", "species_2", "residue_1", "emission_1", etc.
            $table->string('group_key', 50);
            
            // Clave del campo (de la tabla subcategory_fields)
            $table->string('field_key', 100);
            
            // Valor del campo (texto, número, fecha, etc. - todo como string)
            $table->text('value')->nullable();
            
            // Referencias opcionales a tablas relacionadas
            $table->foreignId('species_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('protected_area_id')->nullable()->constrained()->onDelete('set null');
            
            // Orden del campo dentro del grupo
            $table->unsignedInteger('order_index')->default(0);
            
            $table->timestamps();
            
            // Índices
            $table->index(['report_id', 'group_key']);
            $table->index(['report_id', 'field_key']);
            $table->index('group_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_details');
    }
};