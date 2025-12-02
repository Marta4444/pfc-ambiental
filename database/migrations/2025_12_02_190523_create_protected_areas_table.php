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
        Schema::create('protected_areas', function (Blueprint $table) {
            $table->id();
            
            // Identificación
            $table->string('name', 255);
            $table->string('wdpa_id', 50)->nullable()->unique(); // ID en Protected Planet
            
            // Tipo de protección
            $table->string('protection_type', 100); // Parque Nacional, ZEPA, LIC, ZEC, Reserva, etc.
            $table->string('iucn_category', 20)->nullable(); // Ia, Ib, II, III, IV, V, VI
            $table->string('designation', 255)->nullable(); // Designación oficial
            
            // Bounding box (para búsqueda rápida por coordenadas)
            $table->decimal('lat_min', 10, 7)->nullable();
            $table->decimal('lat_max', 10, 7)->nullable();
            $table->decimal('long_min', 10, 7)->nullable();
            $table->decimal('long_max', 10, 7)->nullable();
            
            // Geometría completa (para verificación precisa)
            // Usamos JSON porque MySQL geometry requiere extensiones especiales
            $table->json('geometry')->nullable(); // GeoJSON del polígono
            
            // Información adicional
            $table->text('description')->nullable();
            $table->decimal('area_km2', 12, 4)->nullable(); // Superficie en km²
            $table->string('region', 100)->nullable(); // Comunidad Autónoma
            $table->year('established_year')->nullable(); // Año de declaración
            
            // Fuente y sincronización
            $table->string('source', 100)->default('WDPA'); // WDPA, MITECO, manual
            $table->timestamp('synced_at')->nullable();
            $table->json('source_json')->nullable(); // Datos raw de la API
            
            // Estado
            $table->boolean('active')->default(true);
            
            $table->timestamps();
            
            // Índices para búsqueda geográfica rápida
            $table->index(['lat_min', 'lat_max', 'long_min', 'long_max'], 'idx_bounding_box');
            $table->index('protection_type');
            $table->index('region');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protected_areas');
    }
};