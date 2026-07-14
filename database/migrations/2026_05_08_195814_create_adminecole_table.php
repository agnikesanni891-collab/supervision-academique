<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adminecole', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user')->primary();
            $table->unsignedBigInteger('id_etablissement');

            $table->foreign('id_user')
                  ->references('id_user')
                  ->on('utilisateurs')
                  ->onDelete('cascade');

            $table->foreign('id_etablissement')
                  ->references('id_etablissement')
                  ->on('etablissements')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adminecole');
    }
};