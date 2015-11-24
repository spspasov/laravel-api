<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpeningHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hours', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('venue_id')->unsigned();
            $table->integer('day_of_week')->unsigned();
            $table->time('open_time')->default('00:00:00');
            $table->time('close_time')->default('00:00:00');
            $table->tinyInteger('closed')->default('0');
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
        Schema::drop('hours');
    }
}
