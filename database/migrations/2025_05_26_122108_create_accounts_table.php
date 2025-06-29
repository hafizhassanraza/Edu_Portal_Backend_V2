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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            
            $table->integer('fine')->nullable()->default(0);
            $table->integer('dues')->nullable()->default(0);
            $table->string('discount_type')->nullable()->default('none');// e.g., 'none', 'scholarship', 'teachers_child', 'sibling', etc.
            $table->integer('discount_amount')->nullable()->default(0);
            $table->float('discount_percent')->nullable()->default(0.0);
            $table->string('status')->nullable()->default('active'); // e.g., 'active', 'inactive', 'suspended'
            $table->string('remarks')->nullable(); // Additional notes or remarks about the account

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
        Schema::dropIfExists('accounts');
    }
};
