<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('active')->after('email')->default(0);
            $table->string('device_token')->after('active');
            $table->string('device')->after('active');
            $table->string('ip')->after('active');
            $table->string('phone_number')->after('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropColumn('phone_number');
            $table->dropColumn('ip');
            $table->dropColumn('device');
            $table->dropColumn('device_token');
        });
    }
}
