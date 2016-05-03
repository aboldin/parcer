<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFootballprofitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FootballProfits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('football_match_id')->unsigned()->default(0);
            $table->string('type', 200)->default('');
            $table->float('profit')->default(0);
            $table->string('text')->default('');
            $table->timestamps();
        });

        Schema::table('FootballProfits', function (Blueprint $table) {
            $table->foreign('football_match_id')
                ->references('id')->on('FootballMatches')
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
        Schema::drop('FootballProfits');
    }
}
