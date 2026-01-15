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
        Schema::create('posts_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->string('caption')->nullable();
            $table->unsignedBigInteger('sequence')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at'], 'posts_files_deleted_idx');

            $table->index(['deleted_at', 'post_id'], 'posts_files_list1_idx');
            $table->index(['deleted_at', 'file_id'], 'posts_files_list2_idx');
            $table->index(['deleted_at', 'post_id', 'file_id'], 'posts_files_list3_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_files');
    }
};
