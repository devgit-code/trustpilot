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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->longText("domain")->nullable();
            $table->longText("title")->nullable();
            $table->longText("description")->nullable();
            $table->longText("keywords")->nullable();
            $table->longText("author")->nullable();
            $table->longText("favicons")->nullable();
            $table->longText("screenshot")->nullable();
            $table->longText("categories")->nullable();
            $table->longText("contact_phone")->nullable();
            $table->longText("contact_email")->nullable();
            $table->longText("contact_city")->nullable();
            $table->longText("contact_country")->nullable();
            $table->longText("contact_address")->nullable();
            $table->longText("facebook")->nullable();
            $table->longText("twitter")->nullable();
            $table->longText("instagram")->nullable();
            $table->longText("about_us")->nullable();
            $table->longText("contact_us")->nullable();
            $table->unsignedBigInteger("ratings")->default(0);
            $table->unsignedBigInteger("reviews")->default(0);
            $table->boolean("is_claimed")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
