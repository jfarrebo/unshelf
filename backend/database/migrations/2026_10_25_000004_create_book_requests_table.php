<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sol·licitud d'un exemplar.
     */
    public function up(): void
    {
        Schema::create('book_requests', function (Blueprint $table) {
            $table->id('id_book_request');
            $table->foreignId('id_copy')->constrained('copies', 'id_copy');
            $table->foreignId('id_user_requester')->constrained('users', 'id_user');
            $table->foreignId('id_user_owner')->constrained('users', 'id_user');
            $table->enum('mode', ['prestec', 'donacio']);
            $table->enum('status', ['pendent', 'acceptada', 'rebutjada', 'cancellada', 'en_prestec', 'finalitzada'])
                  ->default('pendent');
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_requests');
    }
};
