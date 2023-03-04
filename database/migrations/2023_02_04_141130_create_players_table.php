<?php

use App\Models\Team;
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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name',25)->unique();
            $table->integer('score')->default(0);
            $table->integer('honor')->default(0);
            $table->integer('prediction')->default(0);
            $table->smallInteger('goals')->default(0);
            $table->smallInteger('assists')->default(0);
            $table->smallInteger('defences')->default(0);
            $table->unsignedTinyInteger('position')->default(1);
            $table->foreignIdFor(Team::class);
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
        Schema::dropIfExists('players');
    }
};
