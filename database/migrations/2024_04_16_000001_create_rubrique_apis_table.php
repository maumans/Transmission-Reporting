<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rubrique_apis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubrique_id')->constrained()->onDelete('cascade');
            $table->string('nom')->nullable();
            $table->string('feuille')->nullable();
            $table->string('groupe');
            $table->string('endpoint');
            $table->string('methode');
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('rubrique_apis');
    }
}; 