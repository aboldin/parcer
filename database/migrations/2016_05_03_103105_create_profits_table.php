<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Profits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('match_id')->unsigned()->default(0);
            $table->string('type', 200)->default('');
            $table->float('profit')->default(0);
            $table->string('text')->default('');
            $table->timestamps();
        });

        Schema::table('Profits', function (Blueprint $table) {
            $table->foreign('match_id')
                ->references('id')->on('Matches')
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
        Schema::drop('Profits');
    }
}
