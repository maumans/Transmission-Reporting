<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Création des permissions
        $permissions = [
            'view_transmissions',
            'create_transmissions',
            'edit_transmissions',
            'delete_transmissions',
            'validate_transmissions',
            'view_reports',
            'create_reports',
            'edit_reports',
            'delete_reports',
            'manage_users',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Création des rôles
        $declarant = Role::create(['name' => 'declarant']);
        $valideur = Role::create(['name' => 'valideur']);
        $admin = Role::create(['name' => 'admin']);

        // Attribution des permissions aux rôles
        $declarant->givePermissionTo([
            'view_transmissions',
            'create_transmissions',
            'edit_transmissions',
            'view_reports',
        ]);

        $valideur->givePermissionTo([
            'view_transmissions',
            'validate_transmissions',
            'view_reports',
            'create_reports',
            'edit_reports',
        ]);

        $admin->givePermissionTo(Permission::all());
    }
} 