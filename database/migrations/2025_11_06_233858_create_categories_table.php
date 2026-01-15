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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('authors')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->boolean('header')->default(true);
            $table->boolean('footer')->default(true);
            $table->string('name', 120);
            $table->string('color', 20)->nullable();
            $table->text('intro')->nullable();
            $table->text('details')->nullable();
            $table->text('notes')->nullable();
            $table->string('image')->nullable();
            $table->json('social')->nullable();
            $table->json('extra_data')->nullable();
            $table->string('key', 15)->nullable()->comment('content key');
            $table->string('type', 15)->nullable()->comment('content type');
            $table->string('slug', 120);
            $table->unsignedBigInteger('sequence')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at'], 'categories_deleted_idx');

            $table->index(['deleted_at', 'status'], 'categories_list1_idx');
            $table->index(['deleted_at', 'status', 'id'], 'categories_list2_idx');
            $table->index(['deleted_at', 'status', 'slug'], 'categories_list3_idx');
            $table->index(['deleted_at', 'type'], 'categories_list4_idx');
            $table->index(['deleted_at', 'type', 'status'], 'categories_list5_idx');
            $table->index(['deleted_at', 'author_id'], 'categories_list6_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
