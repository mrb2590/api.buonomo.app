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
            $table->uuid('id');
            $table->string('name');
            $table->string('extension');
            $table->string('storage_filename');
            $table->string('storage_basename');
            $table->string('storage_path');
            $table->string('mime_type');
            $table->bigInteger('size')->unsigned();
            $table->uuid('folder_id');
            $table->uuid('owned_by_id');
            $table->uuid('created_by_id');
            $table->uuid('updated_by_id');
            $table->softDeletes();
            $table->timestamps();

            $table->primary('id');

            $table->foreign('folder_id')
                ->references('id')
                ->on('drive_folders')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('owned_by_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('created_by_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('cascade');

            $table->foreign('updated_by_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('cascade');

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
        Schema::dropIfExists('drive_files');
    }
}
