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
        Schema::create('ads_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained('ads')->onDelete('cascade');
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at', 'ad_id'], 'ads_domains_list1_idx');
            $table->index(['deleted_at', 'domain_id'], 'ads_domains_list2_idx');
            $table->index(['deleted_at', 'ad_id', 'domain_id'], 'ads_domains_list3_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_domains');
    }
};
