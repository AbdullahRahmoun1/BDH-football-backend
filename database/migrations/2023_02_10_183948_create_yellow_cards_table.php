<?php

use App\Models\Player;
use App\Models\Contest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('yellow_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Player::class);
            $table->foreignIdFor(Contest::class);
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
        Schema::dropIfExists('yellow_cards');
    }
};
