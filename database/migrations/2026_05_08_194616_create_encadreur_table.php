<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encadreur', function (Blueprint $table) {
            $table->unsignedBigInteger('id_memoire');
            $table->unsignedBigInteger('id_etudiant');
            $table->unsignedBigInteger('id_encadrant');

            $table->primary(['id_memoire', 'id_etudiant', 'id_encadrant']);

            $table->foreign('id_memoire')
                  ->references('id_memoire')
                  ->on('memoires')
                  ->onDelete('cascade');

            $table->foreign('id_etudiant')
                  ->references('id_user')
                  ->on('utilisateurs')
                  ->onDelete('cascade');

            $table->foreign('id_encadrant')
                  ->references('id_user')
                  ->on('utilisateurs')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encadreur');
    }
};