<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'client',
            'bus',
            'admin'
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'role' => $role
            ]);
        }
    }
}