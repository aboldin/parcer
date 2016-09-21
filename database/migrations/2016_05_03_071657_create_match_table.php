<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Matches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('league_id')->unsigned()->default(0);
            $table->string('title', 300)->default('');
            $table->string('link', 300)->default('');
            $table->string('full_link', 300)->default('');
            $table->string('link_id', 30)->default('');
            $table->timestamps();

        });
        Schema::table('Matches', function (Blueprint $table) {
            $table->foreign('league_id')
                ->references('id')->on('Leagues')
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
        Schema::drop('Matches');
    }
}
