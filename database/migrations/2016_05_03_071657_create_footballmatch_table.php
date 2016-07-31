<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFootballmatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FootballMatches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('football_league_id')->unsigned()->default(0);
            $table->string('title', 300)->default('');
            $table->string('link', 300)->default('');
            $table->string('link_id', 30)->default('');
            $table->timestamps();

        });
        Schema::table('FootballMatches', function (Blueprint $table) {
            $table->foreign('football_league_id')
                ->references('id')->on('FootballLeagues')
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
        Schema::drop('FootballMatches');
    }
}
