<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auto_reply_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['assigned', 'unassigned']);
            $table->string('keyword')->nullable();
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_reply_templates');
    }
};
