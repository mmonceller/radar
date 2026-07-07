<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('store_name')->after('user_id');
            $table->decimal('price', 10, 2)->after('store_name');
            $table->boolean('is_online')->default(false)->after('price');
            $table->string('latitude')->nullable()->after('is_online');
            $table->string('longitude')->nullable()->after('latitude');
            $table->text('address')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['store_name', 'price', 'is_online', 'latitude', 'longitude', 'address']);
        });
    }
};