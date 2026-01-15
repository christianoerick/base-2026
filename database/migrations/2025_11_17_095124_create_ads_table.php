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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true);
            $table->string('name');
            $table->string('type', 10)->default('image')->comment('image,code');
            $table->string('size', 10)->comment('300x250...');
            $table->text('content')->comment('image path,embed');
            $table->string('url')->nullable();
            $table->boolean('date_fixed')->default(true);
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->boolean('domain_all')->default(true);
            $table->boolean('place_all')->default(false);
            $table->boolean('place_all_categories')->default(false);
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at'], 'ads_deleted_idx');

            $table->index(['deleted_at', 'status'], 'ads_list1_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
