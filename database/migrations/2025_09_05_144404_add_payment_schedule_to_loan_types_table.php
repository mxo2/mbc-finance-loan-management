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
        Schema::table('loan_types', function (Blueprint $table) {
            $table->enum('payment_frequency', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly')->after('loan_term_period');
            $table->integer('payment_day')->default(1)->after('payment_frequency'); // Day of month (1-31) for monthly, day of week (1-7) for weekly
            $table->boolean('auto_start_date')->default(true)->after('payment_day'); // Auto-set start date on approval
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_types', function (Blueprint $table) {
            $table->dropColumn(['payment_frequency', 'payment_day', 'auto_start_date']);
        });
    }
};
