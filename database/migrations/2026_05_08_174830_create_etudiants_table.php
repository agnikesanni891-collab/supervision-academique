<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etudiants', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user')->primary();
            $table->foreign('id_user')
                  ->references('id_user')
                  ->on('utilisateurs')
                  ->onDelete('cascade');
            $table->string('matricule', 50)->unique(); // ✅ Ajouté
            $table->unsignedBigInteger('id_filiere');
            $table->foreign('id_filiere')
                  ->references('id_filiere')
                  ->on('filieres');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etudiants');
    }
};