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
        Schema::create('admin_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('session_id')->nullable()->constrained('admin_sessions')->onDelete('cascade');
            $table->unsignedBigInteger('system_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->string('model');
            $table->unsignedBigInteger('model_id')->nullable()->index();
            $table->string('action', 20);
            $table->string('url')->nullable();
            $table->string('ip_address', 100)->nullable()->index();
            $table->json('data_post')->nullable();
            $table->json('data_extra')->nullable();
            $table->timestamps();

            $table->index(['model', 'model_id']);
            $table->index(['action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_audit');
    }
};
