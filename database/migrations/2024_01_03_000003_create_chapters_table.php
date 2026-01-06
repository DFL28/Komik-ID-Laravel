<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained('manga')->onDelete('cascade');
            $table->string('chapter_number');
            $table->string('title')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamp('source_published_at')->nullable();
            $table->text('pages_data')->nullable(); // JSON array
            $table->timestamps();
            
            $table->index('manga_id');
            $table->index('chapter_number');
            $table->index('source_published_at');
            $table->unique(['manga_id', 'chapter_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
