<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('to', 20)->index();
            $table->string('content', 500);
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index();
            $table->string('external_message_id')->nullable()->index();
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
