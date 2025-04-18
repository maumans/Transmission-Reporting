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
        Schema::create('operation_rubriques', function (Blueprint $table) {
            $table->id();
            $table->boolean('execute')->default(false);
            $table->foreignId('operation_id')->constrained('operations')->cascadeOnDelete();
            $table->foreignId('rubrique_id')->constrained('rubriques')->cascadeOnDelete();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_rubriques');
    }
};
