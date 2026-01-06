<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('manga_id')->constrained('manga')->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->string('chapter_number');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('manga_id');
            $table->unique(['user_id', 'manga_id', 'chapter_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_history');
    }
};
