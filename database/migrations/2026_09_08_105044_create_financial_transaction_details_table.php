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
        Schema::create('financial_transaction_details', function (Blueprint $table) {

            $table->id();

            $table->foreignId('financial_tran_id')
                ->constrained('financial_transactions')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('module_id')->default(1);

            $table->decimal('amount', 18, 2);

            $table->unsignedBigInteger('head_id')->nullable();

            $table->char('crdr', 1);

            $table->string('head_name');

            $table->timestamps();

            $table->index('head_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transaction_details');
    }
};
