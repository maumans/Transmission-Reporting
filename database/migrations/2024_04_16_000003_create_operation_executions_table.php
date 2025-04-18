<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_id')->constrained()->onDelete('cascade');
            $table->foreignId('rubrique_api_id')->constrained()->onDelete('cascade');
            $table->string('statut')->default('en_attente');
            $table->timestamp('date_execution')->nullable();
            $table->text('erreur')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_executions');
    }
}; 