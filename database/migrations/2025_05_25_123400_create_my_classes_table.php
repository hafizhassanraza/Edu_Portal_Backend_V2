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
        Schema::create('my_classes', function (Blueprint $table) {
            $table->id();
            
            $table->string('name')->unique(); // e.g., '9th, 8th, 10th'
            $table->string('status')->default('available'); // e.g., 'active', 'inactive'
            $table->string('disc')->nullable(); // description

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
        Schema::dropIfExists('my_classes');
    }
};
