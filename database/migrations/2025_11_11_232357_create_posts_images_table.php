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
        Schema::create('posts_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('image_id')->constrained('images')->onDelete('cascade');
            $table->boolean('sensitive')->default(false);
            $table->string('caption')->nullable();
            $table->string('author')->nullable();
            $table->json('crop_data')->nullable();
            $table->unsignedBigInteger('sequence')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at'], 'posts_images_deleted_idx');

            $table->index(['deleted_at', 'post_id'], 'posts_images_list1_idx');
            $table->index(['deleted_at', 'image_id'], 'posts_images_list2_idx');
            $table->index(['deleted_at', 'post_id', 'image_id'], 'posts_images_list3_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_images');
    }
};
