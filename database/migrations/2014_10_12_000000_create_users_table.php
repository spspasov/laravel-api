<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->string('phone_number');
            $table->tinyInteger('active');
            $table->integer('accountable_id');
            $table->string('accountable_type');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip_address');
            $table->string('device');
            $table->string('device_token');
            $table->timestamps();
        });

        Schema::create('buses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('image_url');
            $table->text('description');
            $table->text('term');
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
        Schema::drop('users');
        Schema::drop('clients');
        Schema::drop('buses');
    }
}
