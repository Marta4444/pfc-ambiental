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
        Schema::create('report_cost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->string('group_key', 100);
            $table->enum('cost_type', ['VR', 'VE', 'VS']);
            $table->string('concept_name', 255);
            $table->decimal('base_value', 14, 2)->default(0);
            $table->decimal('cr_value', 14, 2)->nullable()->comment('Coste de reposición');
            $table->decimal('gi_value', 6, 4)->nullable()->comment('Índice de gravedad (0-1)');
            $table->decimal('total_cost', 16, 2)->default(0)->comment('Coste total tras aplicar coeficientes');
            $table->json('coef_info_json')->nullable();
            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index(['report_id', 'group_key']);
            $table->index(['report_id', 'cost_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cost_items');
    }
};