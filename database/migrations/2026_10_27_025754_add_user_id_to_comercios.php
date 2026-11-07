<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada comercio pasa a tener dueño. Si se borra el usuario,
     * se borran sus comercios (y con ellos sus transacciones).
     */
    public function up(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->after('id')
                ->constrained()
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
