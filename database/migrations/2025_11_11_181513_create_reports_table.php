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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcategory_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
             $table->string('location'); // nombre del lugar
            $table->string('coordinates')->nullable(); // latitud, longitud
            $table->date('date_damage'); // fecha del daño
            $table->decimal('affected_area', 10, 2)->nullable(); // área afectada en superficie
            $table->tinyInteger('criticallity')->default(1); // nivel de criticidad (1 a 5, por ejemplo)
            $table->string('status')->default('pendiente'); // estado del informe
            $table->string('pdf_report')->nullable(); // ruta del archivo PDF
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
