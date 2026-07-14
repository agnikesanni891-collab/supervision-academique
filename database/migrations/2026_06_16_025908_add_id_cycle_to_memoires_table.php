<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('memoires', function (Blueprint $table) {
        $table->unsignedBigInteger('id_cycle')->nullable()->after('id_filiere');
        $table->foreign('id_cycle')
              ->references('id_cycle')
              ->on('cycles');
    });
}

public function down(): void
{
    Schema::table('memoires', function (Blueprint $table) {
        $table->dropForeign(['id_cycle']);
        $table->dropColumn('id_cycle');
    });
}
};
