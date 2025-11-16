<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User Management
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'users', 'description' => 'View admin users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'group' => 'users', 'description' => 'Create new admin users'],
            ['name' => 'Update Users', 'slug' => 'users.update', 'group' => 'users', 'description' => 'Update admin users'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'group' => 'users', 'description' => 'Delete admin users'],
            ['name' => 'Ban Users', 'slug' => 'users.ban', 'group' => 'users', 'description' => 'Ban/unban admin users'],
            
            // Customer Management
            ['name' => 'View Customers', 'slug' => 'customers.view', 'group' => 'customers', 'description' => 'View customers'],
            ['name' => 'Create Customers', 'slug' => 'customers.create', 'group' => 'customers', 'description' => 'Create customers'],
            ['name' => 'Update Customers', 'slug' => 'customers.update', 'group' => 'customers', 'description' => 'Update customers'],
            ['name' => 'Delete Customers', 'slug' => 'customers.delete', 'group' => 'customers', 'description' => 'Delete customers'],
            
            // Product Management
            ['name' => 'View Products', 'slug' => 'products.view', 'group' => 'products', 'description' => 'View products'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'group' => 'products', 'description' => 'Create products'],
            ['name' => 'Update Products', 'slug' => 'products.update', 'group' => 'products', 'description' => 'Update products'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'group' => 'products', 'description' => 'Delete products'],
            
            // Order Management
            ['name' => 'View Orders', 'slug' => 'orders.view', 'group' => 'orders', 'description' => 'View orders'],
            ['name' => 'Update Orders', 'slug' => 'orders.update', 'group' => 'orders', 'description' => 'Update orders'],
            ['name' => 'Delete Orders', 'slug' => 'orders.delete', 'group' => 'orders', 'description' => 'Delete orders'],
            
            // Category Management
            ['name' => 'View Categories', 'slug' => 'categories.view', 'group' => 'categories', 'description' => 'View categories'],
            ['name' => 'Create Categories', 'slug' => 'categories.create', 'group' => 'categories', 'description' => 'Create categories'],
            ['name' => 'Update Categories', 'slug' => 'categories.update', 'group' => 'categories', 'description' => 'Update categories'],
            ['name' => 'Delete Categories', 'slug' => 'categories.delete', 'group' => 'categories', 'description' => 'Delete categories'],
            
            // Inventory Management
            ['name' => 'View Inventory', 'slug' => 'inventory.view', 'group' => 'inventory', 'description' => 'View inventory'],
            ['name' => 'Manage Inventory', 'slug' => 'inventory.manage', 'group' => 'inventory', 'description' => 'Manage inventory'],
            
            // Site Settings
            ['name' => 'View Settings', 'slug' => 'settings.view', 'group' => 'settings', 'description' => 'View site settings'],
            ['name' => 'Update Settings', 'slug' => 'settings.update', 'group' => 'settings', 'description' => 'Update site settings'],
            
            // Roles & Permissions
            ['name' => 'View Roles', 'slug' => 'roles.view', 'group' => 'roles', 'description' => 'View roles'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'group' => 'roles', 'description' => 'Manage roles and permissions'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Create Super Admin role
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Has all permissions',
                'is_active' => true,
            ]
        );

        // Assign all permissions to Super Admin
        $allPermissions = Permission::all();
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));

        // Create Admin role
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Standard admin with most permissions',
                'is_active' => true,
            ]
        );

        // Assign common admin permissions (excluding role management)
        $adminPermissions = Permission::where('slug', '!=', 'roles.manage')
            ->where('slug', '!=', 'roles.view')
            ->get();
        $adminRole->permissions()->sync($adminPermissions->pluck('id'));

        // Create Manager role
        $managerRole = Role::firstOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Can view and update but not delete',
                'is_active' => true,
            ]
        );

        // Assign view and update permissions to Manager
        $managerPermissions = Permission::where(function ($query) {
            $query->where('slug', 'LIKE', '%.view')
                  ->orWhere('slug', 'LIKE', '%.update');
        })->get();
        $managerRole->permissions()->sync($managerPermissions->pluck('id'));
    }
}
