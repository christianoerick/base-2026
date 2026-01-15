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
        Schema::create('audios', function (Blueprint $table) {
            $table->id();
            $table->boolean('ai')->default(false);
            $table->string('caption');
            $table->string('hash', 40)->unique();
            $table->string('duration')->nullable();
            $table->string('file')->nullable();
            $table->json('file_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at'], 'audios_deleted_idx');
            $table->index(['hash'], 'audios_hash_idx');
            $table->index(['ai'], 'audios_ai_idx');

            $table->index(['deleted_at', 'ai'], 'audios_list1_idx');
            $table->index(['deleted_at', 'hash'], 'audios_list2_idx');
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
