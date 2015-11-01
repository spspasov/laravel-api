<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveLonAndLatFieldsFromRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('pickup_lon');
            $table->dropColumn('pickup_lat');
            $table->dropColumn('setdown_lon');
            $table->dropColumn('setdown_lat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->double('pickup_lon');
            $table->double('pickup_lat');
            $table->double('setdown_lon');
            $table->double('setdown_lat');
        });
    }
}
