<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Permission;
use Vanguard\Role;

class AnnouncementsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $permission = Permission::create([
            'name' => 'announcements.manage',
            'display_name' => 'Manage Announcements',
            'description' => '',
            'removable' => false
        ]);

        Role::where('name', 'Admin')
            ->first()
            ->attachPermission($permission);

        Model::reguard();
    }
}
