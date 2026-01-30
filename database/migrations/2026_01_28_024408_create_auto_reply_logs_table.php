<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_reply_logs', function (Blueprint $table) {
            $table->id();
            $table->string('room_id');
            $table->string('message_id')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_message');
            $table->string('matched_keyword')->nullable();
            $table->text('reply_sent');
            $table->enum('reply_type', ['assigned', 'unassigned']);
            $table->boolean('is_successful')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index('room_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_reply_logs');
    }
};