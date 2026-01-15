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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('authors')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->foreignId('image_id')->nullable()->constrained('images')->onDelete('cascade');
            $table->foreignId('audio_id')->nullable()->constrained('audios')->onDelete('cascade');
            $table->foreignId('file_id')->nullable()->constrained('files')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->boolean('ai')->default(false);
            $table->boolean('category_highlight')->default(false);
            $table->string('title');
            $table->string('subtitle', 500)->nullable();
            $table->string('hat')->nullable();
            $table->string('slug')->nullable();
            $table->string('text_type', 10)->default('simple');
            $table->longText('text')->nullable();
            $table->json('text_items')->nullable();
            $table->string('author')->nullable();
            $table->string('embed')->nullable();
            $table->string('embed_extra')->nullable(); // caso precise adicionar path de thumbnail
            $table->timestamp('publish_date')->nullable();
            $table->boolean('update_status')->default(false);
            $table->timestamp('update_date')->nullable();
            $table->string('key', 15)->nullable()->comment('content key');
            $table->string('type', 15)->nullable()->comment('content type');
            $table->string('image_caption')->nullable();
            $table->string('image_author')->nullable();
            $table->json('image_crop')->nullable();
            $table->string('audio_caption')->nullable();
            $table->string('file_caption')->nullable();
            $table->text('canonical_url')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('integration_id')->nullable();
            $table->unsignedBigInteger('external_id')->nullable(); // para usar em migracoes, quando necessario
            $table->json('extra_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at'], 'posts_deleted_idx');
            $table->index(['type'], 'posts_type_idx');
            $table->index(['key'], 'posts_key_idx');
            $table->index(['integration_id'], 'posts_integration_idx');
            $table->index(['external_id'], 'posts_external_idx');

            $table->index(['deleted_at', 'type'], 'posts_admin1_idx');
            $table->index(['deleted_at', 'key'], 'posts_admin2_idx');
            $table->index(['deleted_at', 'ai'], 'posts_admin3_idx');
            $table->index(['deleted_at', 'update_status'], 'posts_admin4_idx');
            $table->index(['deleted_at', 'update_status', 'ai'], 'posts_admin5_idx');
            $table->index(['deleted_at', 'integration_id'], 'posts_admin6_idx');
            $table->index(['deleted_at', 'external_id'], 'posts_admin7_idx');

            $table->index(['deleted_at', 'status'], 'posts_list1_idx');
            $table->index(['deleted_at', 'status', 'publish_date'], 'posts_list2_idx');
            $table->index(['deleted_at', 'status', 'publish_date', 'id'], 'posts_list3_idx');
            $table->index(['deleted_at', 'status', 'publish_date', 'slug'], 'posts_list4_idx');
            $table->index(['deleted_at', 'status', 'id'], 'posts_list5_idx');
            $table->index(['deleted_at', 'status', 'slug'], 'posts_list6_idx');
            $table->index(['deleted_at', 'status', 'category_id'], 'posts_list7_idx');
            $table->index(['deleted_at', 'status', 'category_id', 'publish_date'], 'posts_list8_idx');
            $table->index(['deleted_at', 'status', 'category_id', 'publish_date', 'category_highlight'], 'posts_list9_idx');
            $table->index(['deleted_at', 'status', 'key', 'publish_date'], 'posts_list10_idx');

            $table->fullText(['title', 'subtitle', 'text', 'hat'], 'posts_fulltext_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
