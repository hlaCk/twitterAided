<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateToDoListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('to_do_lists', function (Blueprint $table) {
            $table->increments('id');
            
            $table->morphs('entity');
            $table->string('action');
            $table->longText('note')->nullable();
			$table->unsignedInteger('user_id')->nullable();
            
            $table->timestamp('run_time')->nullable();
            $table->timestamp('finish_time')->nullable();
            
            $table->string('status')->nullable()->default(\App\Models\Crons\ToDoList::PENDING);
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
        Schema::dropIfExists('to_do_lists');
    }
}
