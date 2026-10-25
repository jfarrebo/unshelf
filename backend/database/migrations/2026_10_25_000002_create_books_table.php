<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Llibre: fitxa única de cada obra o edició.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id('id_book');
            $table->foreignId('id_genre')->constrained('genres', 'id_genre');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('author');
            $table->string('isbn', 17)->nullable()->unique();
            $table->enum('language', ['catala', 'castella', 'angles', 'frances']);
            $table->text('synopsis')->nullable();
            $table->string('cover_path')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('title');
            $table->index('author');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
