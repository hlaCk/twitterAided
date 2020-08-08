<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	return;
        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('t_oauth_token')->nullable();
            $table->string('t_oauth_token_secret')->nullable();
            $table->unsignedInteger('t_id')->nullable();
            $table->unsignedInteger('t_followers_count')->nullable();
            $table->unsignedInteger('t_friends_count')->nullable();
            $table->string('t_screen_name')->nullable();
            $table->string('t_name')->nullable();
            
            // $table->unsignedInteger('user_id')->nullable();
            $table->timestamp('last_update')->nullable();
            
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
		return;
        Schema::dropIfExists('t_users');
    }
}
