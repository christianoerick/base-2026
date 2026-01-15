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
        Schema::create('admin_types', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true);
            $table->boolean('has_category')->default(false);
            $table->string('name', 30);
            $table->string('key', 15);
            $table->string('type', 15)->comment('post,video,audio,gallery,custom,etc');
            $table->unsignedBigInteger('sequence')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_types');
    }
};
