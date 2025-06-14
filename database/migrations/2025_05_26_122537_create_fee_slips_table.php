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
        Schema::create('fee_slips', function (Blueprint $table) {
            $table->id();



            $table->foreignId('fee_id')->constrained('fees')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');

            $table->string( 'challan_number');
            $table->string( 'issue_date');
            $table->string( 'due_date');
            $table->string( 'receiving_date');


            $table->integer('admission_fee')->nullable()->default(0);
            $table->integer('tuition_fee')->nullable()->default(0);
            $table->integer('hostel_fee')->nullable()->default(0);
            $table->integer('exam_fee')->nullable()->default(0);
            $table->integer('transport_fee')->nullable()->default(0);
            $table->integer('library_fee')->nullable()->default(0);
            $table->integer('lab_fee')->nullable()->default(0);
            $table->integer('medical_fee')->nullable()->default(0);
            $table->integer('sports_fee')->nullable()->default(0);
            $table->integer('utility_charges')->nullable()->default(0);
            $table->integer('other')->nullable()->default(0);

            $table->integer('total')->nullable()->default(0);
            $table->integer('fine')->nullable()->default(0);
            $table->integer('dues')->nullable()->default(0);
            $table->integer('discount')->nullable()->default(0);
            $table->string( 'payable')->nullable()->default(0);

            $table->string('paid_amount')->nullable()->default(0);
            $table->string('remaining_amount')->nullable()->default(0);

            $table->string('status')->nullable()->default('unpaid');// e.g., paid, unpaid, partially_paid
            $table->string('payment_method')->nullable()->default('cash'); // cash, cheque, online


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
        Schema::dropIfExists('fee_slips');
    }
};
