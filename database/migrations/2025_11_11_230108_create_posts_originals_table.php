<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts_originals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->foreignId('image_id')->nullable()->constrained('images')->onDelete('cascade');
            $table->string('title');
            $table->string('subtitle', 500)->nullable();
            $table->string('hat')->nullable();
            $table->string('author')->nullable();
            $table->string('image_caption')->nullable();
            $table->string('image_author')->nullable();
            $table->string('slug')->nullable();
            $table->longText('text')->nullable();
            $table->timestamp('publish_date')->nullable();
            $table->text('canonical_url')->nullable();
            $table->unsignedBigInteger('integration_id')->nullable();
            $table->timestamps();

            $table->index(['integration_id'], 'posts_originals_integration_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_originals');
    }
};
