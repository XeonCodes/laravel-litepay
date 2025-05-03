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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string("user_id")->nullable();
            $table->enum('type', ['credit', 'debit'])->nullable();
            $table->decimal("amount", 62, 2)->default(0);
            $table->decimal("fee", 62, 2)->default(0);
            $table->decimal("bonus", 62, 2)->default(0);
            $table->string('ref')->unique()->nullable();
            $table->string('x_ref')->nullable();
            $table->string('session_id')->nullable();
            $table->decimal('amount_sent', 60, 2)->default(0);
            $table->decimal('balance_before', 60, 2)->default(0);
            $table->decimal('balance_after', 60, 2)->default(0);
            $table->decimal('profit', 60, 2)->default(0);
            $table->string('trans_type')->nullable();
            $table->string('merchant')->nullable();
            $table->string('beneficiary')->nullable();
            $table->string('status')->nullable();
            $table->string('sender')->nullable();
            $table->string('product')->nullable();
            $table->string('narration')->nullable();
            $table->string('title')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
