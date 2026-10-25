<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exemplar: la còpia concreta que ofereix cada usuari.
     */
    public function up(): void
    {
        Schema::create('copies', function (Blueprint $table) {
            $table->id('id_copy');
            $table->foreignId('id_book')->constrained('books', 'id_book');
            $table->foreignId('id_user_owner')->constrained('users', 'id_user');
            $table->enum('condition', ['com_nou', 'bon_estat', 'acceptable']);
            $table->enum('mode', ['prestec', 'donacio']);
            $table->unsignedTinyInteger('max_loan_weeks')->nullable(); // 2, 4 o 8 (només préstec)
            $table->enum('status', ['disponible', 'pausat', 'reservat', 'en_prestec', 'cedit', 'retirat'])
                  ->default('disponible');
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('copies');
    }
};
