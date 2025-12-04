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
            $table->string('group_key', 100)->comment('Mismo valor que en report_details para agrupar');
            $table->enum('cost_type', ['VR', 'VE', 'VS'])->comment('Tipo de coste: Valor Reposición, Ecológico, Social');
            $table->string('concept_name', 255)->comment('Nombre del concepto (especie, residuo, etc.)');
            $table->decimal('base_value', 12, 2)->default(0)->comment('Valor base antes de coeficientes');
            $table->decimal('cr_value', 8, 4)->nullable()->comment('Coeficiente de rareza (copiado de report_details)');
            $table->decimal('gi_value', 8, 4)->nullable()->comment('Índice de gravedad calculado');
            $table->decimal('total_cost', 14, 2)->default(0)->comment('Coste total tras aplicar coeficientes');
            $table->json('coef_info_json')->nullable()->comment('Info de coeficientes aplicados');
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