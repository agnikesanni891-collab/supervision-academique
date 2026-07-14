<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memoires', function (Blueprint $table) {
            $table->id('id_memoire');
            $table->string('titre', 255);
            // Dans ta migration de la table memoires
$table->enum('statut', ['cree', 'en_cours', 'soutenu'])->default('cree');

            $table->unsignedBigInteger('id_filiere');
            $table->foreign('id_filiere')
                  ->references('id_filiere')
                  ->on('filieres');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memoires');
    }
};