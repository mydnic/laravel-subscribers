<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('unsubscribe_token', 64)->nullable()->unique()->after('email');
        });

        // Backfill tokens for any existing subscribers
        \Mydnic\Subscribers\Models\Subscriber::withTrashed()
            ->whereNull('unsubscribe_token')
            ->each(function ($subscriber) {
                $subscriber->updateQuietly(['unsubscribe_token' => Str::random(64)]);
            });
    }

    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn('unsubscribe_token');
        });
    }
};
