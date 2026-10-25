<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial d'estats de cada sol·licitud (línia de temps).
     */
    public function up(): void
    {
        Schema::create('request_events', function (Blueprint $table) {
            $table->id('id_request_event');
            $table->foreignId('id_book_request')->constrained('book_requests', 'id_book_request')->cascadeOnDelete();
            $table->enum('status', ['pendent', 'acceptada', 'rebutjada', 'cancellada', 'en_prestec', 'finalitzada']);
            $table->boolean('is_automatic')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_events');
    }
};
