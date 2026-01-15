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
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('internal_created_by')->after('extra_data')->nullable()->comment('criado para informar quando foi criado pelo sistema ou bot');
            $table->unsignedBigInteger('internal_updated_by')->after('internal_created_by')->nullable()->comment('criado para informar quando foi criado pelo sistema ou bot');
            $table->foreignId('created_by')->after('internal_updated_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->after('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('deleted_by')->after('updated_by')->nullable()->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);

            $table->dropColumn([
                'internal_created_by',
                'internal_updated_by',
                'created_by',
                'updated_by',
                'deleted_by',
            ]);
        });
    }
};
