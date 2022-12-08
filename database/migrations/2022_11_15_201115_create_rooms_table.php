<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string("name", 255);
            $table->unsignedBigInteger("building_id");
            $table->unsignedBigInteger("status_id");
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->foreign("building_id")->references("id")->on("buildings");
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
        Schema::dropIfExists('rooms');
    }
}
