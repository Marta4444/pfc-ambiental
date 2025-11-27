<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcategory_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcategory_id')->constrained()->onDelete('cascade');
            $table->foreignId('field_id')->constrained()->onDelete('cascade');
            $table->boolean('is_required')->default(false); // Si el campo es obligatorio
            $table->unsignedInteger('order_index')->default(0); // Orden de visualización
            $table->string('default_value', 255)->nullable(); // Valor por defecto opcional
            $table->timestamps();

            // Comprobación
            $table->unique(['subcategory_id', 'field_id']); // Un field no puede repetirse en una subcategoría
            $table->index('order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcategory_fields');
    }
};
