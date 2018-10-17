<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolesPermissionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->foreign('permission_id')->references('id')->on('permissions')
                ->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->primary(['role_id', 'user_id']);
        });

        // Create roles
        $superRoleId = DB::table('roles')->insertGetId([
            'name' => 'super_user',
            'display_name' => 'Super User',
            'description' => 'A Super User can do anything.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $adminRoleId = DB::table('roles')->insertGetId([
            'name' => 'administrator',
            'display_name' => 'Administrator',
            'description' => 'Administrators are slightly more limited than super users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $memberRoleId = DB::table('roles')->insertGetId([
            'name' => 'member',
            'display_name' => 'Member',
            'description' => 'Member can perform most basic functions.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        // General permissions
        $manageAPIClientsPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'manage_api_clients',
            'display_name' => 'Manage API Clients',
            'description' => 'Create/update/delete clients to consume the API.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        // User permissions
        $createUsersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'create_users',
            'display_name' => 'Create new users',
            'description' => 'Create new users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $fetchUsersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'fetch_users',
            'display_name' => 'Fetch users',
            'description' => 'Fetch users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $updateUsersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'update_users',
            'display_name' => 'Update users',
            'description' => 'Update users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $trashUsersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'trash_users',
            'display_name' => 'Trash users',
            'description' => 'Trash users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $deleteUsersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'delete_users',
            'display_name' => 'Delete users',
            'description' => 'Delete users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        // Super user permissions
        DB::table('permission_role')->insert([
            'permission_id' => $manageAPIClientsPermissionId,
            'role_id' => $superRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $createUsersPermissionId,
            'role_id' => $superRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $fetchUsersPermissionId,
            'role_id' => $superRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $updateUsersPermissionId,
            'role_id' => $superRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $trashUsersPermissionId,
            'role_id' => $superRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $deleteUsersPermissionId,
            'role_id' => $superRoleId
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
}
