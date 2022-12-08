<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("room_id");
            $table->unsignedBigInteger("person_id");
            $table->unsignedBigInteger("status_id");
            $table->dateTime("updated_at")->nullable();
        });

        Schema::table('person_rooms', function (Blueprint $table) {
            $table->foreign("room_id")->references("id")->on("rooms");
            $table->foreign("person_id")->references("id")->on("persons");
            $table->foreign("status_id")->references("id")->on("status");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('person_rooms');
    }
}
