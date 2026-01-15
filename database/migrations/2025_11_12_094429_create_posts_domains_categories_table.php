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
        Schema::create('posts_domains_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('domain_id')->nullable()->constrained('domains')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->softDeletes();

            $table->index(['deleted_at'], 'posts_domains_categories_deleted_idx');

            $table->index(['deleted_at', 'post_id'], 'posts_domains_categories_list1_idx');
            $table->index(['deleted_at', 'domain_id'], 'posts_domains_categories_list2_idx');
            $table->index(['deleted_at', 'category_id'], 'posts_domains_categories_list3_idx');
            $table->index(['deleted_at', 'post_id', 'domain_id'], 'posts_domains_categories_list4_idx');
            $table->index(['deleted_at', 'post_id', 'category_id'], 'posts_domains_categories_list5_idx');
            $table->index(['deleted_at', 'post_id', 'domain_id', 'category_id'], 'posts_domains_categories_list6_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_domains_categories');
    }
};
