<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvatarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avatars', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->unique();
            $table->string('style');
            $table->string('accessories');
            $table->string('clothes_type');
            $table->string('eyebrow_type');
            $table->string('eye_type');
            $table->string('facial_hair_type');
            $table->string('facial_hair_color');
            $table->string('hair_color');
            $table->string('mouth_type');
            $table->string('skin_color');
            $table->string('top_type');
            $table->timestamps();

            $table->primary('id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avatars');
    }
}
