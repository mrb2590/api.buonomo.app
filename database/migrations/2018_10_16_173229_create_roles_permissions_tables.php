<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

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
            $table->uuid('id');
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description');
            $table->timestamps();

            $table->primary('id');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description');
            $table->timestamps();

            $table->primary('id');
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('role_id');

            $table->primary(['permission_id', 'role_id']);

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->uuid('user_id');

            $table->primary(['role_id', 'user_id']);

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        // Roles
        DB::table('roles')->insertGetId([
            'id' => $apiManagerRoleId = Uuid::uuid4()->toString(),
            'name' => 'api_manager',
            'display_name' => 'API Manager',
            'description' => 'Manages API clients.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('roles')->insertGetId([
            'id' => $userManagerRoleId = Uuid::uuid4()->toString(),
            'name' => 'user_manager',
            'display_name' => 'User Manager',
            'description' => 'Manages user accounts.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('roles')->insertGetId([
            'id' => $driveManagerRoleId = Uuid::uuid4()->toString(),
            'name' => 'drive_manager',
            'display_name' => 'Drive Manager',
            'description' => 'Manages drive accounts.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        // API client permissions
        DB::table('permissions')->insertGetId([
            'id' => $manageAPIClientsPermissionId = Uuid::uuid4()->toString(),
            'name' => 'manage_api_clients',
            'display_name' => 'Manage API Clients',
            'description' => 'Create/update/delete clients to consume the API.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        // User permissions
        DB::table('permissions')->insertGetId([
            'id' => $createUsersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'create_users',
            'display_name' => 'Create new users',
            'description' => 'Create new users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $fetchUsersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'fetch_users',
            'display_name' => 'Fetch users',
            'description' => 'Fetch users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $updateUsersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'update_users',
            'display_name' => 'Update users',
            'description' => 'Update users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $trashUsersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'trash_users',
            'display_name' => 'Trash users',
            'description' => 'Trash users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $deleteUsersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'delete_users',
            'display_name' => 'Delete users',
            'description' => 'Delete users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $restoreUsersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'restore_users',
            'display_name' => 'Restore users',
            'description' => 'Restore users.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        // User role permissions
        DB::table('permissions')->insertGetId([
            'id' => $fetchUserRolesPermissionId = Uuid::uuid4()->toString(),
            'name' => 'fetch_user_roles',
            'display_name' => 'Fetch user roles',
            'description' => 'Fetch user roles.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $assignUserRolesPermissionId = Uuid::uuid4()->toString(),
            'name' => 'assign_user_roles',
            'display_name' => 'Assign user roles',
            'description' => 'Assign user roles.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $removeUserRolesPermissionId = Uuid::uuid4()->toString(),
            'name' => 'remove_user_roles',
            'display_name' => 'Remove user roles',
            'description' => 'Remove user roles.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        // Folder permissions
        DB::table('permissions')->insertGetId([
            'id' => $createFoldersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'create_folders',
            'display_name' => 'Create new folders',
            'description' => 'Create new folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $fetchFoldersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'fetch_folders',
            'display_name' => 'Fetch folders',
            'description' => 'Fetch folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $updateFoldersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'update_folders',
            'display_name' => 'Update folders',
            'description' => 'Update folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $moveFoldersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'move_folders',
            'display_name' => 'Move folders',
            'description' => 'Move folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $downloadFoldersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'download_folders',
            'display_name' => 'Download folders',
            'description' => 'Download folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $trashFoldersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'trash_folders',
            'display_name' => 'Trash folders',
            'description' => 'Trash folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $deleteFoldersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'delete_folders',
            'display_name' => 'Delete folders',
            'description' => 'Delete folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $restoreFoldersPermissionId = Uuid::uuid4()->toString(),
            'name' => 'restore_folders',
            'display_name' => 'Restore folders',
            'description' => 'Restore folders.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        // File permissions
        DB::table('permissions')->insertGetId([
            'id' => $uploadFilesPermissionId = Uuid::uuid4()->toString(),
            'name' => 'upload_files',
            'display_name' => 'Create new files',
            'description' => 'Create new files.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $fetchFilesPermissionId = Uuid::uuid4()->toString(),
            'name' => 'fetch_files',
            'display_name' => 'Fetch files',
            'description' => 'Fetch files.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $updateFilesPermissionId = Uuid::uuid4()->toString(),
            'name' => 'update_files',
            'display_name' => 'Update files',
            'description' => 'Update files.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $moveFilesPermissionId = Uuid::uuid4()->toString(),
            'name' => 'move_files',
            'display_name' => 'Move files',
            'description' => 'Move files.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $downloadFilesPermissionId = Uuid::uuid4()->toString(),
            'name' => 'download_files',
            'display_name' => 'Download files',
            'description' => 'Download files.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $trashFilesPermissionId = Uuid::uuid4()->toString(),
            'name' => 'trash_files',
            'display_name' => 'Trash files',
            'description' => 'Trash files.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $deleteFilesPermissionId = Uuid::uuid4()->toString(),
            'name' => 'delete_files',
            'display_name' => 'Delete files',
            'description' => 'Delete files.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('permissions')->insertGetId([
            'id' => $restoreFilesPermissionId = Uuid::uuid4()->toString(),
            'name' => 'restore_files',
            'display_name' => 'Restore files',
            'description' => 'Restore files.',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        // API Manager's role permissions
        DB::table('permission_role')->insert([
            'permission_id' => $manageAPIClientsPermissionId,
            'role_id' => $apiManagerRoleId,
        ]);

        // User Manager's role permissions
        DB::table('permission_role')->insert([
            'permission_id' => $createUsersPermissionId,
            'role_id' => $userManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $fetchUsersPermissionId,
            'role_id' => $userManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $updateUsersPermissionId,
            'role_id' => $userManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $trashUsersPermissionId,
            'role_id' => $userManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $deleteUsersPermissionId,
            'role_id' => $userManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $restoreUsersPermissionId,
            'role_id' => $userManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $fetchUserRolesPermissionId,
            'role_id' => $userManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $assignUserRolesPermissionId,
            'role_id' => $userManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $removeUserRolesPermissionId,
            'role_id' => $userManagerRoleId,
        ]);

        // Drive Manager's role permissions
        DB::table('permission_role')->insert([
            'permission_id' => $createFoldersPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $fetchFoldersPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $updateFoldersPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $moveFoldersPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $downloadFoldersPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $trashFoldersPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $deleteFoldersPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $restoreFoldersPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $uploadFilesPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $fetchFilesPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $updateFilesPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $moveFilesPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $downloadFilesPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $trashFilesPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $deleteFilesPermissionId,
            'role_id' => $driveManagerRoleId,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $restoreFilesPermissionId,
            'role_id' => $driveManagerRoleId,
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
