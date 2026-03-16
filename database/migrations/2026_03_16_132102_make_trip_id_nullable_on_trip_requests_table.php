<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->unsignedBigInteger('trip_id')->nullable()->change();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->unsignedBigInteger('trip_id')->nullable(false)->change();
            $table->foreign('trip_id')->references('id')->on('trips')->cascadeOnDelete();
        });
    }
};
