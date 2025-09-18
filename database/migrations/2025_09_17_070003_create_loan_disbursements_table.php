<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_disbursements', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_id');
            $table->enum('transaction_type', ['file_charges', 'loan_amount'])->default('loan_amount');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable(); // bank_transfer, cash, cheque, etc.
            $table->string('transaction_reference')->nullable(); // UTR, cheque number, etc.
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->date('transaction_date');
            $table->text('transaction_notes')->nullable();
            $table->string('receipt_document')->nullable(); // uploaded receipt/proof
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->integer('recorded_by'); // staff member who recorded this
            $table->integer('verified_by')->nullable(); // manager who verified this
            $table->timestamp('verified_at')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_disbursements');
    }
};
