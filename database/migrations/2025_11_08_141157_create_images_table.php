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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->boolean('ai')->default(false);
            $table->string('caption');
            $table->string('author')->nullable();
            $table->string('hash', 40)->unique();
            $table->string('file')->nullable();
            $table->json('file_data')->nullable();
            $table->boolean('crop_status')->default(false);
            $table->longText('crop_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at'], 'images_deleted_idx');
            $table->index(['hash'], 'images_hash_idx');
            $table->index(['ai'], 'images_ai_idx');

            $table->index(['deleted_at', 'ai'], 'images_list1_idx');
            $table->index(['deleted_at', 'hash'], 'images_list2_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
