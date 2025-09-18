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
            $table->decimal('file_charges_amount', 10, 2)->default(0)->after('amount');
            $table->enum('file_charges_status', ['pending', 'paid', 'waived'])->default('pending')->after('file_charges_amount');
            $table->timestamp('file_charges_paid_at')->nullable()->after('file_charges_status');
            $table->enum('disbursement_status', ['pending', 'disbursed'])->default('pending')->after('file_charges_paid_at');
            $table->timestamp('disbursed_at')->nullable()->after('disbursement_status');
            $table->text('disbursement_notes')->nullable()->after('disbursed_at');
            $table->integer('disbursed_by')->default(0)->after('disbursement_notes');
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
            $table->dropColumn([
                'file_charges_amount', 
                'file_charges_status', 
                'file_charges_paid_at',
                'disbursement_status',
                'disbursed_at',
                'disbursement_notes',
                'disbursed_by'
            ]);
        });
    }
};
