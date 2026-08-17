<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('digikala_url')->nullable()->after('meta');
            $table->string('digikala_product_id')->nullable()->index()->after('digikala_url');
            $table->unsignedBigInteger('digikala_variant_id')->nullable()->after('digikala_product_id');
            $table->boolean('digikala_auto_sync')->default(false)->index()->after('digikala_variant_id');
            $table->timestamp('digikala_last_synced_at')->nullable()->after('digikala_auto_sync');
            $table->string('digikala_last_sync_status')->nullable()->after('digikala_last_synced_at');
            $table->text('digikala_last_sync_message')->nullable()->after('digikala_last_sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'digikala_url',
                'digikala_product_id',
                'digikala_variant_id',
                'digikala_auto_sync',
                'digikala_last_synced_at',
                'digikala_last_sync_status',
                'digikala_last_sync_message',
            ]);
        });
    }
};
