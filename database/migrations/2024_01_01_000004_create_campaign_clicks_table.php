<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('kanpen.tables.campaign_clicks'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_delivery_id')->constrained(config('kanpen.tables.campaign_deliveries'))->cascadeOnDelete();
            $table->string('url');
            $table->timestamp('clicked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('kanpen.tables.campaign_clicks'));
    }
};
