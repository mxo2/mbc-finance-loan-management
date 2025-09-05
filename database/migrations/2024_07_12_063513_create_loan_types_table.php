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
        Schema::create('loan_types', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->float('min_loan_amount')->default(0);
            $table->float('max_loan_amount')->default(0);
            $table->string('interest_type')->nullable();
            $table->float('interest_rate')->default(0);
            $table->integer('max_loan_term')->default(0);
            $table->string('loan_term_period')->nullable();
            $table->float('penalties')->default(0);
            $table->integer('status')->default(0);
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('loan_types');
    }
};
