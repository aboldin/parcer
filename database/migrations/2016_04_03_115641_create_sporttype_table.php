<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSporttypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('SportTypes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 300)->default('');
            $table->string('url', 300)->default('');
            $table->timestamps();
        });

        DB::table('SportTypes')->insert(
            array(
                'id' => 1,
                'name' => 'Football',
                'url' => 'football'
            )
        );

        DB::table('SportTypes')->insert(
            array(
                'id' => 2,
                'name' => 'Tennis',
                'url' => 'tennis'
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('SportTypes');
    }
}
