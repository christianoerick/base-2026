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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->boolean('ai')->default(false);
            $table->string('caption');
            $table->string('hash', 40)->unique();
            $table->string('file')->nullable();
            $table->json('file_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at'], 'files_deleted_idx');
            $table->index(['hash'], 'files_hash_idx');
            $table->index(['ai'], 'files_ai_idx');

            $table->index(['deleted_at', 'ai'], 'files_list1_idx');
            $table->index(['deleted_at', 'hash'], 'files_list2_idx');
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
