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

        // 2. Insert Dummy Admin User
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

        // 3. Assign eQurban-Admin role to user
        // Using role_code because id_role_access is not found in the actual database table user_roles
        DB::table('user_roles')->where('id_user', $admin->id_user)->delete();
        
        DB::table('user_roles')->insert([
            'id_user' => $admin->id_user,
            'role_code' => 'eQurban-Admin',
            'status' => 'active',
            'created_by' => 'seeder',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->command->info('Roles and Admin User seeded successfully!');
    }
}
