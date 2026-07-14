<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versions', function (Blueprint $table) {
            $table->id('id_version');
            $table->integer('numero_version');
            $table->string('url_fichier', 500);         // URL Cloudinary
            $table->string('public_id_cloudinary', 300)->nullable(); // pour supprimer sur Cloudinary
            $table->integer('taille_fichier');           // taille en Ko
            $table->enum('statut_version', [
                'soumis',
                'accepte',
                'rejete'
            ])->default('soumis');
            $table->longText('texte_extrait');

            $table->unsignedBigInteger('id_memoire');
            $table->foreign('id_memoire')
                  ->references('id_memoire')
                  ->on('memoires')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('id_etudiant');
            $table->foreign('id_etudiant')
                  ->references('id_user')
                  ->on('utilisateurs');

            $table->timestamp('date_depot')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versions');
    }
};