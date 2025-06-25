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
        Schema::create('employee_qualifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Assuming employees table exists
            $table->string('staff_id')->unique(); // auto genrated staff ID, e.g., EMP-001, EMP-002, etc.
            $table->string('role'); // e.g., teacher, staff, accountant, guard, etc.
            $table->string('qualification'); // e.g., B.Ed, M.Sc, MBA
            $table->string('institute'); // e.g., University of XYZ, ABC College
            $table->date('year_of_passing'); // e.g., 2020-01-15
            $table->date('joining_date')->nullable(); // Date when the employee joined, nullable if not applicable
            $table->string('specialization')->nullable(); // e.g., Mathematics, Science, etc. Nullable if not applicable
            $table->string('department')->nullable(); // e.g., Science, Administration, Security, etc. Nullable if not applicable
            $table->string('experience')->nullable(); // Number of years of experience, nullable if not applicable
            $table->string('grade')->nullable(); // e.g., A, B, C, etc. Nullable if not applicable
            $table->string('remarks')->nullable(); // Optional remarks about the qualification
            $table->string('document_path')->nullable(); // Path to the document if applicable, e.g., a scanned copy of the certificate




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
        Schema::dropIfExists('employee_qualifications');
    }
};
