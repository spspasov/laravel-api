<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameClientIdColumnInRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropForeign('requests_client_id_foreign');
            $table->renameColumn('client_id', 'user_id');
            $table->foreign('user_id')->references('id')->on('clients')->onDelete('cascade');
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
            $table->dropForeign('requests_user_id_foreign');
            $table->renameColumn('user_id', 'client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }
}
