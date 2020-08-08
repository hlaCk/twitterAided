<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->string('url')->nullable();
			$table->tinyInteger('verified')->nullable();
			$table->string('profile_image_url')->nullable();
			$table->string('profile_image_url_https')->nullable();
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
        Schema::dropIfExists('profiles');
    }
}
