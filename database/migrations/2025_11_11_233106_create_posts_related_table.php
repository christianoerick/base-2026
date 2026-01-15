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
        Schema::create('posts_related', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('related_id')->constrained('posts')->onDelete('cascade');
            $table->unsignedBigInteger('sequence')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at'], 'posts_related_deleted_idx');

            $table->index(['deleted_at', 'post_id'], 'posts_related_list1_idx');
            $table->index(['deleted_at', 'related_id'], 'posts_related_list2_idx');
            $table->index(['deleted_at', 'post_id', 'related_id'], 'posts_related_list3_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_related');
    }
};
