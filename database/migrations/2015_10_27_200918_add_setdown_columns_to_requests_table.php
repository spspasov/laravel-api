<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSetdownColumnsToRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->renameColumn('lat', 'pickup_lat');
            $table->renameColumn('lon', 'pickup_lon');

            $table->double('setdown_lat')->after('lon');
            $table->double('setdown_lon')->after('setdown_lat');
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
            $table->renameColumn('pickup_lat', 'lat');
            $table->renameColumn('pickup_lon', 'lon');

            $table->dropColumn('setdown_lat');
            $table->dropColumn('setdown_lon');
        });
    }
}
