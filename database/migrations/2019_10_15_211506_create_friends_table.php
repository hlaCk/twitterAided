<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friends', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('user_id');
	
			$table->string('name')->nullable();
			$table->string('screen_name')->nullable();
			$table->string('location')->nullable();
			$table->string('description')->nullable();
			$table->string('url')->nullable();
			$table->string('profile_image_url')->nullable();
			$table->string('profile_image_url_https')->nullable();
	
			$table->double('followers_count')->nullable()->default(0);
			$table->double('friends_count')->nullable()->default(0);
			$table->double('listed_count')->nullable()->default(0);
			$table->double('favourites_count')->nullable()->default(0);
			$table->double('statuses_count')->nullable()->default(0);
	
			$table->tinyInteger('following')->nullable();
			$table->tinyInteger('follow_request_sent')->nullable();
			$table->tinyInteger('notifications')->nullable();
			$table->tinyInteger('verified')->nullable();
			$table->tinyInteger('protected')->nullable();
			$table->tinyInteger('suspended')->nullable();
			$table->tinyInteger('needs_phone_verification')->nullable();
	
			$table->timestamp('creation_date')->nullable();
	
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
        Schema::dropIfExists('friends');
    }
}
