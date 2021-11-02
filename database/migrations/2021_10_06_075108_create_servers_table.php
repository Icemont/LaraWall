<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('ip', 15)->unique();
            $table->string('hostname', 150)->index()->nullable();
            $table->boolean('status')->default(1)->index();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->json('attributes')->nullable();
            $table->string('isp', 150)->nullable();
            $table->tinyText('note')->nullable();
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
        Schema::dropIfExists('servers');
    }
}
