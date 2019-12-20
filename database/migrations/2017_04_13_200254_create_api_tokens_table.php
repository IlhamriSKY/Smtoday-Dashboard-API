<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->string('id', 40);
            $table->unsignedInteger('user_id');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();

            $table->primary('id');
            $table->index(['id', 'expires_at']);
        });

        Schema::table('api_tokens', function (Blueprint $table) {
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
            Schema::table('api_tokens', function (Blueprint $table) {
                $table->dropForeign('api_tokens_user_id_foreign');
            });
        }

        Schema::dropIfExists('api_tokens');
    }
}
