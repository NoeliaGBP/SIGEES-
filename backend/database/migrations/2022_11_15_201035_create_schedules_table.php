<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->dateTime("start_time");
            $table->dateTime("end_time");
            $table->unsignedBigInteger("person_id");
            $table->boolean("status");
        });
        
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign("person_id")->references("id")->on("persons");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
}
