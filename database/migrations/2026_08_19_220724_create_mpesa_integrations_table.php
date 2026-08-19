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
        Schema::create('mpesa_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('environment')->default('sandbox')->index();
            $table->text('consumer_key');
            $table->text('consumer_secret');
            $table->string('shortcode');
            $table->text('passkey');
            $table->string('transaction_type')->default('CustomerPayBillOnline');
            $table->boolean('is_active')->default(false)->index();
            $table->string('connection_status')->default('untested')->index();
            $table->timestamp('last_tested_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_integrations');
    }
};
