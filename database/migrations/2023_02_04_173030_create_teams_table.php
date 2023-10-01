<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name',40)->unique();
            $table->string('grade',5);
            $table->string('class',5);
            $table->integer('diff')->default(0);
            $table->integer('points')->default(0);
            $table->tinyInteger('stage')->default(1);
            $table->smallInteger('wins')->default(0);
            $table->smallInteger('ties')->default(0);
            $table->smallInteger('losses')->default(0);
            $table->string('logo',55)->nullable()->default("default.png");
            $table->boolean('disqualified')->default(false);
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
        Schema::dropIfExists('teams');
    }
};
