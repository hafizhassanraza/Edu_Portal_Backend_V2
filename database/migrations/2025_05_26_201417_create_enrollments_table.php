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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('my_classes')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->foreignId('fee_slip_id')->constrained('fee_slips')->onDelete('cascade');//admission fee slip id

            $table->string('reg_number')->unique();
            $table->date('enrollment_date')->nullable(); // Date of enrollment
            $table->string( 'academic_year')->nullable(); 

            $table->integer('sibling_in')->default(0);// Indicates if a sibling is already enrolled in the same class. like 1 for yes, 0 for no
            $table->string('previous_school')->nullable(); // Name of the previous school if applicable
            $table->string('status')->nullable()->default('active'); // e.g., active, inactive, graduated, transferred
            $table->string('remarks')->nullable(); // Additional notes or remarks about the enrollment
            $table->integer('hostel_admission')->default(0);// Indicates if the student is admitted to the hostel. like 1 for yes, 0 for no
            $table->integer( 'transport_admission')->default(0);// Indicates if the student is admitted to the transport service. like 1 for yes, 0 for no

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
        Schema::dropIfExists('enrollments');
    }
};
