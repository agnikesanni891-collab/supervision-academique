<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('id_notification');
            $table->enum('type_evenement', [
                'depot',
                'annotation',
                'acceptation',
                'rejet',
                'soutenance'
            ]);
            $table->string('message', 500);
            $table->boolean('est_lue')->default(false);

            // Destinataire
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')
                  ->references('id_user')
                  ->on('utilisateurs')
                  ->onDelete('cascade');

            // Version concernée (optionnel)
            $table->unsignedBigInteger('id_version')->nullable();
            $table->foreign('id_version')
                  ->references('id_version')
                  ->on('versions')
                  ->onDelete('set null');

            $table->timestamp('date_envoi')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};