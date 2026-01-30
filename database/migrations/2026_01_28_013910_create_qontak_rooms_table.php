<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qontak_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_id')->unique();
            $table->string('agent_id')->nullable();
            $table->boolean('is_assigned')->default(false);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            
            $table->index('room_id');
            $table->index('is_assigned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qontak_rooms');
    }
};