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
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true);
            $table->string('name', 120);
            $table->text('intro')->nullable();
            $table->text('details')->nullable();
            $table->string('image')->nullable();
            $table->json('social')->nullable();
            $table->string('slug', 120);
            $table->json('extra_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at'], 'authors_deleted_idx');

            $table->index(['deleted_at', 'status'], 'authors_list1_idx');
            $table->index(['deleted_at', 'status', 'id'], 'authors_list2_idx');
            $table->index(['deleted_at', 'status', 'slug'], 'authors_list3_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
