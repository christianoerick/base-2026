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
        Schema::create('getlate_accounts', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true);
            $table->string('name', 100);
            $table->string('profile_id')->nullable();
            $table->json('accounts')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('getlate_accounts');
    }
};
