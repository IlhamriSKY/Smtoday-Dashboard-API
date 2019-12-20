<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_activity', function (Blueprint $table) {
            $table->increments('id');
            $table->text('description');
            $table->unsignedInteger('user_id');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamp('created_at');
        });

        Schema::table('user_activity', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::getDriverName() != 'sqlite') {
            Schema::table('user_activity', function (Blueprint $table) {
                $table->dropForeign('user_activity_user_id_foreign');
            });
        }

        Schema::drop('user_activity');

        \DB::table('permissions')->where('name', 'users.activity')->delete();
    }
}
