<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriveFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drive_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('basename');
            $table->string('path')->unique();
            $table->string('filename');
            $table->string('extension');
            $table->string('mime_type');
            $table->bigInteger('size')->unsigned();
            $table->integer('folder_id')->unsigned()->nullable();
            $table->integer('owned_by_id')->unsigned();
            $table->integer('created_by_id')->unsigned();
            $table->integer('updated_by_id')->unsigned();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('drive_folders')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('owned_by_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by_id')->references('id')->on('users')
                ->onDelete('no action')->onUpdate('cascade');
            $table->foreign('updated_by_id')->references('id')->on('users')
                ->onDelete('no action')->onUpdate('cascade');
            $table->unique(['name', 'folder_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
