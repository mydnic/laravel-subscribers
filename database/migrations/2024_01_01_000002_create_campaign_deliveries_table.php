<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('kanpen.tables.campaign_deliveries'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained(config('kanpen.tables.campaigns'))->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained(config('kanpen.tables.subscribers'))->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'subscriber_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('kanpen.tables.campaign_deliveries'));
    }
};
