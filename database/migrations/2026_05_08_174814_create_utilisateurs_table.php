<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email', 150)->unique();
            $table->string('mot_de_passe', 255);
            $table->enum('role', [
                'etudiant',
                'encadrant',
                'admin_ecole',
                'admin_plateforme'
            ]);
            $table->boolean('est_actif')->default(true);
            $table->timestamps(); // created_at sert de date_creation
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};