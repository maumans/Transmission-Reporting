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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->date('date_arretee')->nullable();
            $table->foreignId('declarant_id')->nullable()->constrained('users');
            $table->foreignId('valideur_id')->nullable()->constrained('users');
            $table->string('fichier_balance')->nullable();
            $table->timestamp('date_transmission')->nullable();
            $table->json('reponse_api')->nullable();
            $table->enum('statut',['CREATION','MODIFICATION','ANNULATION'])->default('CREATION');
            $table->enum('etat',['en_attente','validee','rejetee','transmise'])->default('en_attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
