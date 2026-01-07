<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manga', function (Blueprint $table) {
            $table->string('artist')->nullable()->after('author');
            $table->string('serialization')->nullable()->after('artist');
        });
    }

    public function down(): void
    {
        Schema::table('manga', function (Blueprint $table) {
            $table->dropColumn(['artist', 'serialization']);
        });
    }
};
