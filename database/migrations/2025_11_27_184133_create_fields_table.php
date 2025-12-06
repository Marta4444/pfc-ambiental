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
            $table->string('key_name', 100)->unique(); 
            $table->string('label', 255); 
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
            $table->string('units', 50)->nullable(); 
            $table->json('options_json')->nullable(); 
            $table->text('help_text')->nullable(); 
            $table->string('placeholder', 255)->nullable(); 
            $table->boolean('is_numeric')->default(false); 
            $table->boolean('active')->default(true); 
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
