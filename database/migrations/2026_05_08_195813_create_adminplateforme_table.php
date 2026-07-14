<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adminplateforme', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user')->primary();
            $table->foreign('id_user')
                  ->references('id_user')
                  ->on('utilisateurs')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adminplateforme');
    }
};