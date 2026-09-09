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
        Schema::create('financial_transactions', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('import_id');

            $table->unsignedBigInteger('module_id')->default(1);

            $table->string('tran_id');

            $table->decimal('amount', 18, 2);

            $table->char('crdr', 1);

            $table->date('tran_date');

            $table->string('acad_year')->nullable();

            $table->string('fee_category')->nullable();

            $table->tinyInteger('entry_mode')->default(0);

            $table->unsignedBigInteger('brid')->nullable();

            $table->timestamps();

            $table->unique([
                'import_id',
                'tran_id'
            ]);

            $table->index('tran_date');

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
        Schema::dropIfExists('financial_transactions');
    }
};
