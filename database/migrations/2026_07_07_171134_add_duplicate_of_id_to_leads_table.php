<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Self-referencing foreign key linking duplicate rows to the primary row
            $table->foreignId('duplicate_of_id')->nullable()->constrained('leads')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['duplicate_of_id']);
            $table->dropColumn('duplicate_of_id');
        });
    }
};