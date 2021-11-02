<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('nickname')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->boolean('legal')->default(0)->index();
            $table->string('company')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('country')->nullable();
            $table->boolean('status')->default(1)->index();
            $table->json('attributes')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('customers');
    }
}
