<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_votes', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('type');
            $table->text('explanation')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('lead_votes', function (Blueprint $table) {
            $table->dropColumn(['reason', 'explanation']);
        });
    }
};
