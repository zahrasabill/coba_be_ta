<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleandPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar permissions
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'assign roles',
            'view data',
            'konsultasi pasien',
            'konsultasi dokter',
        ];

        // Buat permission
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Buat roles dan assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        $editorRole = Role::firstOrCreate(['name' => 'dokter']);
        $editorRole->givePermissionTo(['view users', 'create users', 'edit users', 'view data']);

        $userRole = Role::firstOrCreate(['name' => 'pasien']);
        $userRole->givePermissionTo(['view data', 'konsultasi pasien']);

        $this->command->info('Roles dan Permissions berhasil dibuat!');
    }
}
