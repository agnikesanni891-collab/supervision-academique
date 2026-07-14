<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annotations', function (Blueprint $table) {
            $table->id('id_annotation');
            $table->integer('page');
            $table->decimal('position_x', 10, 2);
            $table->decimal('position_y', 10, 2);
            $table->text('texte_commentaire');

            $table->unsignedBigInteger('id_version');
            $table->foreign('id_version')
                  ->references('id_version')
                  ->on('versions')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('id_encadrant');
            $table->foreign('id_encadrant')
                  ->references('id_user')
                  ->on('utilisateurs');

            $table->timestamp('date_annotation')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotations');
    }
};