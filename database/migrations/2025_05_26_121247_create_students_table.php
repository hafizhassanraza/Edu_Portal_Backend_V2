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
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('father_name');
            $table->string('gender');
            $table->string('dob')->nullable(); 
            $table->string('b_form')->unique();
            $table->string('address')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('health_profile')->nullable();
            $table->string('photo')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->default('12345678'); // Default password, should be changed after first login
            $table->string('status')->nullable();// e.g., 'active', 'inactive'

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
        Schema::dropIfExists('students');
    }
};
