<?php

use App\Models\Avatar;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Create my test account
        $email = 'mrb2590@gmail.com';
        $user = new User;

        $user->first_name = 'Mike';
        $user->last_name = 'Buonomo';
        $user->email = $email;
        $user->username = 'wesl3ypipes';
        $user->password = bcrypt('apples');
        $user->email_verified_at = Carbon::now();
        $user->allocated_drive_bytes = 107374182400; // 100 GB

        $user->save();

        $user->assignRole('api_manager');
        $user->assignRole('user_manager');
        $user->assignRole('drive_manager');

        $user->createRootFolder();
        $user->createRandomAvatar();

        // Create 50 random users
        factory(User::class, 500)->create()->each(function ($user) use ($faker) {
            // $user->assignRole($faker->randomElement(['administrator', 'member']));
            $user->createRootFolder();
            $user->createRandomAvatar();
        });
    }
}
