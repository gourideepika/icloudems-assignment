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
        Schema::create('staging_ledgers', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('import_id');

            $table->integer('sr_no')->nullable();

            $table->date('operation_date')->nullable();

            $table->string('academic_year')->nullable();

            $table->string('session')->nullable();

            $table->string('alloted_category')->nullable();

            $table->string('voucher_type')->nullable();

            $table->string('voucher_no')->nullable();

            $table->string('roll_no')->nullable();

            $table->string('admno')->nullable();

            $table->string('status')->nullable();

            $table->string('fee_category')->nullable();

            $table->string('faculty')->nullable();

            $table->string('program')->nullable();

            $table->string('department')->nullable();

            $table->string('batch')->nullable();

            $table->string('receipt_no')->nullable();

            $table->string('fee_head')->nullable();

            $table->decimal('due_amount', 18, 2)->default(0);

            $table->decimal('paid_amount', 18, 2)->default(0);

            $table->decimal('concession', 18, 2)->default(0);

            $table->decimal('scholarship_amount', 18, 2)->default(0);

            $table->decimal('reverse_concession_amount', 18, 2)
                ->default(0);

            $table->decimal('write_off', 18, 2)->default(0);

            $table->decimal('adjusted_amount', 18, 2)->default(0);

            $table->decimal('refund_amount', 18, 2)->default(0);

            $table->decimal('fund_transfer_amount', 18, 2)
                ->default(0);

            $table->text('remark')->nullable();

            $table->timestamps();

            $table->index('import_id');
            $table->index('voucher_no');
            $table->index('admno');
            $table->index('voucher_type');

            $table->foreign('import_id')
                ->references('id')
                ->on('imports')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staging_ledgers');
    }
};
