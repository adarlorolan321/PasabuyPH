<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->decimal('parcel_length_cm', 8, 2)->nullable()->after('price_offer');
            $table->decimal('parcel_width_cm', 8, 2)->nullable()->after('parcel_length_cm');
            $table->decimal('parcel_height_cm', 8, 2)->nullable()->after('parcel_width_cm');
            $table->decimal('parcel_weight_kg', 8, 2)->nullable()->after('parcel_height_cm');
            $table->string('parcel_photo_path')->nullable()->after('parcel_weight_kg');
        });
    }

    public function down(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->dropColumn([
                'parcel_length_cm',
                'parcel_width_cm',
                'parcel_height_cm',
                'parcel_weight_kg',
                'parcel_photo_path',
            ]);
        });
    }
};

