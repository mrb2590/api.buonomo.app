<?php

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
        $user->slug = str_slug(explode('@', $email)[0], '-');
        $user->password = bcrypt('apples');
        $user->email_verified_at = Carbon::now();

        $user->save();

        $user->assignRole('api_manager');
        $user->assignRole('user_manager');

        // Create 50 random users
        factory(User::class, 50)->create();
    }
}
