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
        Schema::table('conversation_flows', function (Blueprint $table) {
            $table->enum('current_state', [
                'idle',
                'main_menu',
                'browsing_categories',
                'browsing_events',
                'browsing_courses',
                'selected_event',
                'viewing_event_details',
                'selecting_payment_method',
                'awaiting_payment',
                'awaiting_payment_number',
                'awaiting_payment_confirmation',
                'payment_confirmation',
                'registration_name',
                'registration_email',
                'registration_complete',
                'viewing_help',
            ])->default('idle')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_flows', function (Blueprint $table) {
            $table->enum('current_state', [
                'idle',
                'main_menu',
                'browsing_categories',
                'browsing_events',
                'browsing_courses',
                'viewing_event_details',
                'selecting_payment_method',
                'awaiting_payment',
                'payment_confirmation',
                'registration_name',
                'registration_email',
                'registration_complete',
            ])->default('idle')->change();
        });
    }
};
