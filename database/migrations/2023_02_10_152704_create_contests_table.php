<?php

use App\Models\Contest;
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
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Team::class,'firstTeam_id');
            $table->foreignIdFor(Team::class,'secondTeam_id');
            $table->string('place',25)->default(Contest::PLAY_GROUND);
            $table->unsignedTinyInteger('period')->default(1);
            $table->boolean('league')->default(true);
            $table->tinyInteger('stage')->default(1);
            $table->tinyInteger('firstTeamScore')->default(-1);
            $table->tinyInteger('secondTeamScore')->default(-1);
            $table->tinyInteger('firstTeamThatScored')->default(-1);
            $table->date('date')->nullable();
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
        Schema::dropIfExists('contests');
    }
};
