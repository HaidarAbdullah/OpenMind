<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users');
            $table->string('title');
            $table->string('abstract');
            $table->text('content');
            $table->string('img_src')->nullable();
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('views')->default(1);
            $table->boolean('is_public')->default(1);
            $table->string('magazine_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
};
