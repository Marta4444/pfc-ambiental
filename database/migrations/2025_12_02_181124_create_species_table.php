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
            $table->string('taxon_group', 100)->nullable(); // Mamíferos, Aves, Reptiles, Anfibios, Peces, Invertebrados, Flora
            
            // Protección BOE (nacional)
            $table->string('boe_status', 100)->nullable(); // En peligro de extinción, Vulnerable, etc.
            $table->string('boe_law_ref', 255)->nullable(); // Real Decreto 139/2011, Ley 42/2007, etc.
            
            // Protección CCAA (autonómica)
            $table->json('ccaa_status')->nullable(); // {"Andalucía": "En peligro", "Cataluña": "Vulnerable", ...}
            
            // IUCN Red List
            $table->string('iucn_category', 20)->nullable(); // EX, EW, CR, EN, VU, NT, LC, DD, NE
            $table->year('iucn_assessment_year')->nullable();
            
            // CITES
            $table->string('cites_appendix', 10)->nullable(); // I, II, III, null
            
            // Metadatos de sincronización
            $table->timestamp('synced_at')->nullable(); // Última sincronización con APIs
            $table->json('source_json')->nullable(); // Datos raw de las APIs para debug/auditoría
            
            // Control de origen
            $table->boolean('is_protected')->default(false); // True si tiene alguna protección
            $table->boolean('manually_added')->default(false); // True si fue añadida manualmente
            
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