<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->string('image_url');
            $table->string('state');
            $table->double('lat');
            $table->double('lon');
            $table->timestamps();
        });

        Schema::create('bus_region', function (Blueprint $table) {

            $table->integer('bus_id')->unsigned()->index();
            $table->foreign('bus_id')->references('id')->on('buses')->onDelete('cascade');

            $table->integer('region_id')->unsigned()->index();
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');

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
        Schema::drop('bus_region');
        Schema::drop('regions');
    }
}
