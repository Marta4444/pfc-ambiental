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
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            
            // Identificación taxonómica
            $table->string('scientific_name', 255)->unique();
            $table->string('common_name', 255)->nullable();
            $table->string('taxon_group', 100)->nullable(); 
            
            // Protección BOE (nacional)
            $table->string('boe_status', 100)->nullable(); 
            $table->string('boe_law_ref', 255)->nullable(); 
            
            // Protección CCAA (autonómica)
            $table->json('ccaa_status')->nullable(); 
            
            // IUCN Red List
            $table->string('iucn_category', 20)->nullable(); 
            $table->year('iucn_assessment_year')->nullable();
            
            // CITES
            $table->string('cites_appendix', 10)->nullable(); 
            
            // Metadatos de sincronización
            $table->timestamp('synced_at')->nullable(); 
            $table->json('source_json')->nullable(); 
            
            // Control de origen
            $table->boolean('is_protected')->default(false); 
            $table->boolean('manually_added')->default(false); 
            
            $table->timestamps();
            
            // Índices para búsqueda rápida
            $table->index('common_name');
            $table->index('taxon_group');
            $table->index('boe_status');
            $table->index('iucn_category');
            $table->index('is_protected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('species');
    }
};