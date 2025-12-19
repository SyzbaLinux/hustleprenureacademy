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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('whatsapp_verified')->default(false)->after('phone_number');
            $table->timestamp('whatsapp_verified_at')->nullable()->after('whatsapp_verified');
            $table->timestamp('last_whatsapp_interaction')->nullable()->after('whatsapp_verified_at');
            $table->string('preferred_language', 5)->default('en')->after('last_whatsapp_interaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_verified',
                'whatsapp_verified_at',
                'last_whatsapp_interaction',
                'preferred_language'
            ]);
        });
    }
};
