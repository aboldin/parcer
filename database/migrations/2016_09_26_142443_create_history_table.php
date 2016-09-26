<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('History', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sport_type', 50)->default('');
            $table->string('league', 100)->default('');
            $table->string('match', 100)->default('');
            $table->string('type', 30)->default('');
            $table->float('profit')->default(0);
            $table->string('text')->default('');
            $table->string('full_link', 300)->default('');
            $table->datetime('match_date');
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
        Schema::drop('History');
    }
}
