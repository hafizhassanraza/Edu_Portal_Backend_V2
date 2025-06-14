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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');

            $table->string('name');
            $table->string('phone_number')->nullable();
            $table->string('relation');// e.g., Father, Mother, Guardian, etc.
            $table->string('cnic')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('occupation')->nullable();// e.g., Teacher, Engineer, Doctor, etc.
            $table->string('status')->default('active'); // e.g., 'active', 'inactive'
            $table->string('photo')->nullable(); // Path to the guardian's photo

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
        Schema::dropIfExists('guardians');
    }
};
