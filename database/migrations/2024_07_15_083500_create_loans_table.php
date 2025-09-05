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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_id')->default(0);
            $table->integer('loan_type')->default(0);
            $table->integer('customer')->default(0);
            $table->date('loan_start_date')->nullable();
            $table->date('loan_due_date')->nullable();
            $table->float('amount')->default(0);
            $table->text('purpose_of_loan')->nullable();
            $table->integer('loan_terms')->default(0);
            $table->string('loan_term_period')->nullable();
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->integer('parent_id')->default(0);
            $table->integer('branch_id')->default(0);
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
        Schema::dropIfExists('loans');
    }
};
