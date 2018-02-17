<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\User;

class UserTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_superadmin = Role::create(['name' => 'superadmin']);
        $role_admin = Role::create(['name' => 'admin']);
        $role_student = Role::create(['name' => 'student']);
        /**
         * @var User $user
         */
        $user =  User::create([
            'name' => 'Суперадмин',
            'password' => bcrypt('123123'),
            'email' => 'y@y.ru'
        ]);
        $user->assignRole('superadmin');

    }
}