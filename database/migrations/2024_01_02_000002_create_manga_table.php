<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manga', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('alternative_title')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('author')->nullable();
            $table->string('status')->default('ongoing'); // ongoing, completed
            $table->string('type')->default('Manga'); // Manga, Manhwa, Manhua
            $table->text('genres')->nullable(); // comma-separated
            $table->decimal('rating', 3, 2)->default(0);
            $table->string('country')->nullable();
            $table->string('content_type')->nullable();
            $table->timestamp('last_chapter_at')->nullable();
            $table->timestamps();
            
            $table->index('slug');
            $table->index('status');
            $table->index('type');
            $table->index('last_chapter_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manga');
    }
};
