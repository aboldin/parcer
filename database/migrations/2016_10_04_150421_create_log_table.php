<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('proxy_id')->unsigned()->default(0);
            $table->integer('code')->unsigned()->default(0);
            $table->text('response')->default('');
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
        Schema::drop('Log');
    }
}
