<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->string('key_name', 100)->unique(); // Nombre clave del campo (ej: 'tree_species', 'water_ph')
            $table->string('label', 255); // Etiqueta legible del campo
            $table->enum('type', [
                'text', 
                'textarea', 
                'number', 
                'decimal', 
                'select', 
                'multiselect', 
                'checkbox', 
                'radio', 
                'date', 
                'time', 
                'datetime', 
                'file', 
                'boolean'
            ])->default('text');
            $table->string('units', 50)->nullable(); // Unidades (ej: 'kg', 'm²', '€', 'litros')
            $table->json('options_json')->nullable(); // Opciones para select/multiselect/radio (JSON array)
            $table->text('help_text')->nullable(); // Texto de ayuda/descripción
            $table->string('placeholder', 255)->nullable(); // Placeholder para inputs
            $table->boolean('is_numeric')->default(false); // Si el campo es numérico (para validaciones y cálculos)
            $table->boolean('active')->default(true); // Si el campo está activo
            $table->timestamps();

            // Índices
            $table->index('key_name');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
