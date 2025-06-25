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
        Schema::create('result_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('result_id')->constrained('results')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->integer('marks_obtained')->default(0); // Marks obtained by the student
            $table->integer('total_marks')->default(100); // Total marks for the exam
            $table->string('grade')->nullable(); // Grade based on marks obtained
            $table->string('remarks')->nullable(); // Optional remarks for the result record
            $table->enum('attendance_status', ['present', 'absent'])->default('present');
            
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
        Schema::dropIfExists('result_records');
    }
};
