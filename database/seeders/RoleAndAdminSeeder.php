<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoleAccess;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RoleAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Insert Role Accesses
        $roles = [
            ['role_name' => 'eQurban SuperAdmin', 'role_code' => 'eQurban-SuperAdmin'],
            ['role_name' => 'eQurban Admin', 'role_code' => 'eQurban-Admin'],
            ['role_name' => 'eQurban Customer', 'role_code' => 'eQurban-Customer'],
            ['role_name' => 'eQurban Finance', 'role_code' => 'eQurban-Finance'],
        ];

        foreach ($roles as $role) {
            RoleAccess::updateOrCreate(
                ['role_code' => $role['role_code']],
                [
                    'role_name' => $role['role_name'],
                    'status' => 'active',
                    'created_by' => 'seeder'
                ]
            );
        }

        // 2. Insert SuperAdmin User
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@localhost'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone' => '08111111111',
                'password' => Hash::make('superadmin123'),
                'status' => 'active',
                'created_by' => 'seeder'
            ]
        );

        // Assign SuperAdmin role
        DB::table('user_roles')->where('id_user', $superAdmin->id_user)->delete();
        DB::table('user_roles')->insert([
            'id_user' => $superAdmin->id_user,
            'role_code' => 'eQurban-SuperAdmin',
            'status' => 'active',
            'created_by' => 'seeder',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 3. Insert Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@localhost'],
            [
                'first_name' => 'Admin',
                'last_name' => 'QurbanHub',
                'phone' => '08123456789',
                'password' => Hash::make('admin123'),
                'status' => 'active',
                'created_by' => 'seeder'
            ]
        );

        // Assign Admin role
        DB::table('user_roles')->where('id_user', $admin->id_user)->where('role_code', 'eQurban-Admin')->delete();
        DB::table('user_roles')->insert([
            'id_user' => $admin->id_user,
            'role_code' => 'eQurban-Admin',
            'status' => 'active',
            'created_by' => 'seeder',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->command->info('Roles, SuperAdmin and Admin User seeded successfully!');
    }
}
