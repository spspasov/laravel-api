<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('addressable_id')->index()->unsigned()->nullable();
            $table->string('addressable_type')->nullable();
            $table->tinyInteger('type')->unsigned();
            $table->string('suburb');
            $table->string('street_number');
            $table->string('street_name');
            $table->integer('postcode')->unsigned();
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
        Schema::drop('addresses');
    }
}
