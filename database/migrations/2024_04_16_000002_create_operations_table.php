<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->date('date_arretee')->nullable();
            $table->foreignId('declarant_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('valideur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('rubrique_id')->nullable()->constrained('rubriques')->cascadeOnDelete();
            $table->json('donnees')->nullable();
            $table->string('fichier')->nullable();
            $table->timestamp('date_transmission')->nullable();
            $table->json('reponse_api')->nullable();
            $table->enum('statut',['CREATION','MODIFICATION','ANNULATION'])->default('CREATION');
            $table->enum('etat',['en_attente','validee','rejetee','transmise'])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
