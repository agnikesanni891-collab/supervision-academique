<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration  // ✅ syntaxe anonyme (Laravel 9+)
{
    public function up(): void
    {
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id('id_item');
            $table->foreignId('id_memoire')->constrained('memoires', 'id_memoire')->onDelete('cascade');
            $table->string('libelle');
            $table->string('categorie')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('est_complete')->default(false);
            $table->timestamp('date_completion')->nullable();
            $table->unsignedBigInteger('complete_par')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->foreign('complete_par')->references('id_user')->on('utilisateurs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};