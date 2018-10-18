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
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('role_id')->references('id')->on('roles')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->foreign('role_id')->references('id')->on('roles')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->primary(['role_id', 'user_id']);
        });

        // Roles
        $apiManagerRoleId = DB::table('roles')->insertGetId([
            'name' => 'api_manager',
            'display_name' => 'API Manager',
            'description' => 'Manages API clients.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $userManagerRoleId = DB::table('roles')->insertGetId([
            'name' => 'user_manager',
            'display_name' => 'User Manager',
            'description' => 'Manages user accounts.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $driveManagerRoleId = DB::table('roles')->insertGetId([
            'name' => 'drive_manager',
            'display_name' => 'Drive Manager',
            'description' => 'Manages drive accounts.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        // API client permissions
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

        $restoreUsersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'restore_users',
            'display_name' => 'Restore users',
            'description' => 'Restore users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        // User role permissions
        $fetchUserRolesPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'fetch_user_roles',
            'display_name' => 'Fetch user roles',
            'description' => 'Fetch user roles.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $assignUserRolesPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'assign_user_roles',
            'display_name' => 'Assign user roles',
            'description' => 'Assign user roles.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $removeUserRolesPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'remove_user_roles',
            'display_name' => 'Remove user roles',
            'description' => 'Remove user roles.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        // Folder permissions
        $createFoldersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'create_folders',
            'display_name' => 'Create new folders',
            'description' => 'Create new folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $fetchFoldersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'fetch_folders',
            'display_name' => 'Fetch folders',
            'description' => 'Fetch folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $updateFoldersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'update_folders',
            'display_name' => 'Update folders',
            'description' => 'Update folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $moveFoldersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'move_folders',
            'display_name' => 'Move folders',
            'description' => 'Move folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $downloadFoldersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'download_folders',
            'display_name' => 'Download folders',
            'description' => 'Download folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $trashFoldersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'trash_folders',
            'display_name' => 'Trash folders',
            'description' => 'Trash folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $deleteFoldersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'delete_folders',
            'display_name' => 'Delete folders',
            'description' => 'Delete folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        $restoreFoldersPermissionId = DB::table('permissions')->insertGetId([
            'name' => 'restore_folders',
            'display_name' => 'Restore folders',
            'description' => 'Restore folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        // API Manager's role permissions
        DB::table('permission_role')->insert([
            'permission_id' => $manageAPIClientsPermissionId,
            'role_id' => $apiManagerRoleId
        ]);

        // User Manager's role permissions
        DB::table('permission_role')->insert([
            'permission_id' => $createUsersPermissionId,
            'role_id' => $userManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $fetchUsersPermissionId,
            'role_id' => $userManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $updateUsersPermissionId,
            'role_id' => $userManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $trashUsersPermissionId,
            'role_id' => $userManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $deleteUsersPermissionId,
            'role_id' => $userManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $restoreUsersPermissionId,
            'role_id' => $userManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $fetchUserRolesPermissionId,
            'role_id' => $userManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $assignUserRolesPermissionId,
            'role_id' => $userManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $removeUserRolesPermissionId,
            'role_id' => $userManagerRoleId
        ]);

        // Drive Manager's role permissions
        DB::table('permission_role')->insert([
            'permission_id' => $createFoldersPermissionId,
            'role_id' => $driveManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $fetchFoldersPermissionId,
            'role_id' => $driveManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $updateFoldersPermissionId,
            'role_id' => $driveManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $moveFoldersPermissionId,
            'role_id' => $driveManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $downloadFoldersPermissionId,
            'role_id' => $driveManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $trashFoldersPermissionId,
            'role_id' => $driveManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $deleteFoldersPermissionId,
            'role_id' => $driveManagerRoleId
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $restoreFoldersPermissionId,
            'role_id' => $driveManagerRoleId
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
