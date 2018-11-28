<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriveFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drive_folders', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->uuid('folder_id')->nullable();
            $table->bigInteger('size')->unsigned()->default(0);
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

            $table->unique(['name', 'folder_id', 'owned_by_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('folder_id')->nullable()->after('password');

            $table->foreign('folder_id')
                ->references('id')
                ->on('drive_folders')
                ->onDelete('set null')
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_folder_id_foreign');

            $table->dropColumn('folder_id');
        });

        Schema::dropIfExists('drive_folders');
    }
}
