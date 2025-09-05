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
        Schema::table('loans', function (Blueprint $table) {
            $table->integer('created_by')->default(0)->after('notes');
        });

        Schema::table('repayment_schedules', function (Blueprint $table) {
            $table->string('transaction_id')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('receipt')->nullable();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->text('sms_message')->nullable();
            $table->integer('enabled_sms')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
        Schema::table('repayment_schedules', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
            $table->dropColumn('payment_type');
            $table->dropColumn('receipt');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('sms_message');
            $table->dropColumn('enabled_sms');
        });
    }
};
