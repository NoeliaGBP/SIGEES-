<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean("status");
            $table->unsignedBigInteger("role_id");
            $table->unsignedBigInteger("person_id");
            $table->rememberToken();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign("role_id")->references("id")->on("roles");
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
        Schema::dropIfExists('users');
    }
}
