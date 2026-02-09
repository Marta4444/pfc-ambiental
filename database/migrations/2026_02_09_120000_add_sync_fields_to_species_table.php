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
        Schema::table('species', function (Blueprint $table) {
            // IDs externos de las APIs
            $table->unsignedBigInteger('gbif_key')->nullable()->after('is_protected');
            $table->unsignedBigInteger('iucn_taxon_id')->nullable()->after('gbif_key');
            $table->string('cites_id', 50)->nullable()->after('iucn_taxon_id');
            
            // Control de sincronización
            $table->string('sync_source', 50)->nullable()->after('cites_id'); // gbif, iucn, cites, manual
            $table->string('sync_status', 20)->default('pending')->after('sync_source'); // pending, synced, error
            $table->text('sync_error')->nullable()->after('sync_status');
            $table->timestamp('last_sync_attempt')->nullable()->after('sync_error');
            
            // Datos adicionales de taxonomía
            $table->string('kingdom', 100)->nullable()->after('taxon_group');
            $table->string('phylum', 100)->nullable()->after('kingdom');
            $table->string('class', 100)->nullable()->after('phylum');
            $table->string('order', 100)->nullable()->after('class');
            $table->string('family', 100)->nullable()->after('order');
            $table->string('genus', 100)->nullable()->after('family');
            
            // Índices adicionales
            $table->index('gbif_key');
            $table->index('iucn_taxon_id');
            $table->index('sync_status');
            $table->index('family');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->dropIndex(['gbif_key']);
            $table->dropIndex(['iucn_taxon_id']);
            $table->dropIndex(['sync_status']);
            $table->dropIndex(['family']);
            
            $table->dropColumn([
                'gbif_key',
                'iucn_taxon_id',
                'cites_id',
                'sync_source',
                'sync_status',
                'sync_error',
                'last_sync_attempt',
                'kingdom',
                'phylum',
                'class',
                'order',
                'family',
                'genus',
            ]);
        });
    }
};
