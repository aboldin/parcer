<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableParcerSwitcher extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ParcerSwitcher', function (Blueprint $table) {
            $table->boolean('enabled')->default(0);
            $table->primary('enabled');
            $table->string('ip', 300)->default('');
        });

        DB::table('ParcerSwitcher')->insert(
            array(
                'enabled' => 0,
                'ip' => '188.225.77.40'
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
        Schema::drop('ParcerSwitcher');
    }
}
