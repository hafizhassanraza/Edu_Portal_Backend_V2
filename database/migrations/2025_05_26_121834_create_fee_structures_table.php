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
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('class_id')->constrained('my_classes')->onDelete('cascade');

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
        Schema::dropIfExists('fee_structures');
    }
};
