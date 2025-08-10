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
        $table->dateTime('dateDemande');
        $table->dateTime('dateSortie');
        $table->dateTime('dateRetour');
        $table->unsignedBigInteger('statutId');
        $table->timestamps();

        //  clé étrangère 
        $table->foreign('statutId')->references('id')->on(table: 'statuts')->onDelete('cascade');
         $table->foreign('emailEmployee')->references('email')->on(table: 'users')->onDelete('cascade');

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
