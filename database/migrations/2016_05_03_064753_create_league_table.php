<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeagueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Leagues', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 300)->default('');
            $table->string('link', 300)->default('');
            $table->integer('sport_type_id')->unsigned()->default(0);
            $table->integer('count')->default(0);
            $table->timestamps();
        });
        Schema::table('Leagues', function (Blueprint $table) {
            $table->foreign('sport_type_id')
                ->references('id')->on('SportTypes')
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
        Schema::drop('Leagues');
    }
}
