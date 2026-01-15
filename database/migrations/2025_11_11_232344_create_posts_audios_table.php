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
        Schema::create('posts_audios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('audio_id')->constrained('audios')->onDelete('cascade');
            $table->string('caption')->nullable();
            $table->unsignedBigInteger('sequence')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at'], 'posts_audios_deleted_idx');

            $table->index(['deleted_at', 'post_id'], 'posts_audios_list1_idx');
            $table->index(['deleted_at', 'audio_id'], 'posts_audios_list2_idx');
            $table->index(['deleted_at', 'post_id', 'audio_id'], 'posts_audios_list3_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_audios');
    }
};
