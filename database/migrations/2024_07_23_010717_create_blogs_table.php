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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->longText("title")->nullable();
            $table->longText("slug")->nullable();
            $table->longText("excerpt")->nullable();
            $table->longText("content")->nullable();
            $table->enum("status", ["draft", "published"])->default("draft");
            $table->longText("featured_image")->nullable();
            $table->longText("categories")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
