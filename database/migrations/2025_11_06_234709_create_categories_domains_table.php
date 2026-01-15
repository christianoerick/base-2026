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
        Schema::create('categories_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->softDeletes();

            $table->index(['deleted_at'], 'categories_domains_deleted_idx');

            $table->index(['deleted_at', 'category_id'], 'categories_domains_list1_idx');
            $table->index(['deleted_at', 'domain_id'], 'categories_domains_list2_idx');
            $table->index(['deleted_at', 'category_id', 'domain_id'], 'categories_domains_list3_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories_domains');
    }
};
