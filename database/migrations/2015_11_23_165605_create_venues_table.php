<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVenuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
*/
    public function up()
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('type')->unsigned();
            $table->string('image_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('url')->nullable();
            $table->string('instagram_username')->nullable();
            $table->string('twitter_username')->nullable();
            $table->integer('facebook_id')->unsigned()->nullable();
            $table->text('description')->nullable();
            $table->boolean('accepts_online_bookings')->default(0);
            $table->string('abn')->nullable();
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
        Schema::drop('venues');
    }
}
