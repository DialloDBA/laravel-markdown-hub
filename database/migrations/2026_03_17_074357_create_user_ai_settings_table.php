<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('selected_model')->nullable();
            $table->text('custom_api_key')->nullable(); // user's own key (optional)
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('user_ai_settings'); }
};
