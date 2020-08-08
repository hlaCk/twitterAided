<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
			$table->increments('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
	
			$table->string('t_oauth_token')->nullable();
			$table->string('t_oauth_token_secret')->nullable();
			$table->string('t_id')->nullable();
			$table->string('t_followers_count')->nullable();
			$table->string('t_friends_count')->nullable();
			$table->string('t_screen_name')->nullable();
			$table->string('t_name')->nullable();
	
			// $table->unsignedInteger('user_id')->nullable();
			$table->timestamp('last_update')->nullable();
			$table->string('status')->default(\App\User::STATUS['ACTIVE']);
			
			$table->string('token_key')->nullable();
	
	
			$table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
