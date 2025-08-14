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
    Schema::create('demandes', function (Blueprint $table) {
        $table->id();
        $table->string('raison');
        $table->string('emailEmployee');

        // Types corrigés
        $table->date('dateDemande');           // Juste la date
        $table->time('dateSortie');            // Juste l'heure
        $table->date('dateSortiePause');       // Juste la date
        $table->time('dateRetour');           
      

        $table->unsignedBigInteger('statutId');
        $table->timestamps();

        // Clés étrangères
        $table->foreign('statutId')
              ->references('id')
              ->on('statuts')
              ->onDelete('cascade');

        $table->foreign('emailEmployee')
              ->references('email')
              ->on('users')
              ->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};
