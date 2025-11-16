# Role and Permission System Documentation

## Table of Contents
1. [Overview](#overview)
2. [Database Structure](#database-structure)
3. [Models](#models)
4. [Middleware](#middleware)
5. [Usage Examples](#usage-examples)
6. [API Endpoints](#api-endpoints)
7. [Seeder Information](#seeder-information)
8. [Best Practices](#best-practices)

---

## Overview

The Role and Permission system provides a flexible way to manage access control for admin users. It supports:

- **Roles**: Group permissions together (e.g., Super Admin, Admin, Manager)
- **Permissions**: Granular access control (e.g., `users.view`, `products.create`)
- **Direct User Permissions**: Assign permissions directly to users (bypassing roles)
- **Role-based Permissions**: Assign permissions through roles
- **Middleware Protection**: Protect routes with permission checks

### Key Features

- Admin users have all permissions by default
- Users can have permissions through roles AND direct assignments
- Permissions are checked in order: Direct permissions → Role permissions
- Flexible permission grouping (users, products, orders, etc.)

---

## Database Structure

### Tables

#### 1. `roles` Table
Stores role definitions.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | Role name (e.g., "Super Admin") |
| `slug` | string | Unique slug (e.g., "super-admin") |
| `description` | text | Role description |
| `is_active` | boolean | Whether the role is active |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

#### 2. `permissions` Table
Stores permission definitions.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | Permission name (e.g., "View Users") |
| `slug` | string | Unique slug (e.g., "users.view") |
| `description` | text | Permission description |
| `group` | string | Permission group (e.g., "users", "products") |
| `is_active` | boolean | Whether the permission is active |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

#### 3. `permission_role` Table
Pivot table linking permissions to roles.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `permission_id` | bigint | Foreign key to permissions |
| `role_id` | bigint | Foreign key to roles |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

#### 4. `permission_user` Table
Pivot table linking permissions directly to users.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `permission_id` | bigint | Foreign key to permissions |
| `user_id` | bigint | Foreign key to users |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

#### 5. `users` Table (Updated)
Added `role_id` column to link users to roles.

| Column | Type | Description |
|--------|------|-------------|
| `role_id` | bigint | Foreign key to roles (nullable) |

---

## Models

### Role Model

**Location**: `app/Models/Role.php`

#### Relationships

```php
// Get all permissions for a role
$role->permissions();

// Get all users with this role
$role->users();
```

#### Methods

```php
// Check if role has a specific permission
$role->hasPermission(string $permissionSlug): bool
```

#### Example Usage

```php
use App\Models\Role;

// Get a role
$role = Role::where('slug', 'admin')->first();

// Check if role has permission
if ($role->hasPermission('users.create')) {
    // Role has permission
}

// Get all permissions for a role
$permissions = $role->permissions;
```

### Permission Model

**Location**: `app/Models/Permission.php`

#### Relationships

```php
// Get all roles that have this permission
$permission->roles();

// Get all users that have this permission directly
$permission->users();
```

#### Example Usage

```php
use App\Models\Permission;

// Get a permission
$permission = Permission::where('slug', 'users.create')->first();

// Get all roles with this permission
$roles = $permission->roles;

// Get all users with this permission
$users = $permission->users;
```

### User Model

**Location**: `app/Models/User.php`

#### Relationships

```php
// Get the role model for the user
$user->roleModel();

// Get direct permissions for the user
$user->permissions();
```

#### Methods

```php
// Check if user is admin (legacy method, still works)
$user->isAdmin(): bool

// Check if user has a specific permission
$user->hasPermission(string $permissionSlug): bool

// Check if user has any of the given permissions
$user->hasAnyPermission(array $permissionSlugs): bool

// Check if user has all of the given permissions
$user->hasAllPermissions(array $permissionSlugs): bool
```

#### Example Usage

```php
use App\Models\User;

// Get authenticated user
$user = auth()->user();

// Check if user is admin (has all permissions)
if ($user->isAdmin()) {
    // User is admin
}

// Check if user has a specific permission
if ($user->hasPermission('users.create')) {
    // User can create users
}

// Check if user has any of the permissions
if ($user->hasAnyPermission(['users.create', 'users.update'])) {
    // User can create OR update users
}

// Check if user has all of the permissions
if ($user->hasAllPermissions(['users.create', 'users.update'])) {
    // User can create AND update users
}

// Get user's role
$role = $user->roleModel;

// Get user's direct permissions
$permissions = $user->permissions;
```

---

## Middleware

### CheckPermission Middleware

**Location**: `app/Http/Middleware/CheckPermission.php`

**Alias**: `permission`

#### Usage in Routes

```php
// Protect route with a single permission
Route::get('/users', [UserController::class, 'index'])
    ->middleware('permission:users.view');

// Protect route with multiple permissions (user needs any one)
Route::get('/products', [ProductController::class, 'index'])
    ->middleware('permission:products.view|products.manage');

// Combine with other middleware
Route::middleware(['auth:sanctum', 'permission:users.create'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
});
```

#### How It Works

1. Checks if user is authenticated
2. If user is admin (`role === 'admin'`), grants access automatically
3. Otherwise, checks if user has the required permission
4. Returns 403 if permission is missing

#### Example Route Protection

```php
// In routes/api.php

// Single permission check
Route::middleware(['auth:sanctum', 'permission:users.view'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});

// Multiple routes with same permission
Route::middleware(['auth:sanctum', 'permission:products.manage'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});
```

---

## Usage Examples

### Example 1: Assign Role to User

```php
use App\Models\User;
use App\Models\Role;

// Get user and role
$user = User::find(1);
$role = Role::where('slug', 'admin')->first();

// Assign role to user
$user->role_id = $role->id;
$user->save();
```

### Example 2: Assign Permission Directly to User

```php
use App\Models\User;
use App\Models\Permission;

// Get user and permission
$user = User::find(1);
$permission = Permission::where('slug', 'users.create')->first();

// Assign permission directly to user
$user->permissions()->attach($permission->id);

// Or sync multiple permissions
$permissions = Permission::whereIn('slug', ['users.create', 'users.update'])->pluck('id');
$user->permissions()->sync($permissions);
```

### Example 3: Assign Permissions to Role

```php
use App\Models\Role;
use App\Models\Permission;

// Get role
$role = Role::where('slug', 'manager')->first();

// Get permissions
$permissions = Permission::whereIn('slug', [
    'users.view',
    'products.view',
    'orders.view'
])->pluck('id');

// Assign permissions to role
$role->permissions()->sync($permissions);
```

### Example 4: Check Permission in Controller

```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function create(Request $request)
    {
        // Check permission in controller
        if (!auth()->user()->hasPermission('users.create')) {
            return response()->json([
                'message' => 'You do not have permission to create users'
            ], 403);
        }

        // Create user logic here
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        // Check multiple permissions
        if (!$user->hasAnyPermission(['users.update', 'users.manage'])) {
            return response()->json([
                'message' => 'Insufficient permissions'
            ], 403);
        }

        // Update user logic here
    }
}
```

### Example 5: Conditional Logic Based on Permissions

```php
use App\Models\User;

$currentUser = auth()->user();

// Show different UI based on permissions
$canCreateUsers = $currentUser->hasPermission('users.create');
$canDeleteUsers = $currentUser->hasPermission('users.delete');
$canManageUsers = $currentUser->hasAllPermissions([
    'users.create',
    'users.update',
    'users.delete'
]);

// In Blade template or API response
return response()->json([
    'permissions' => [
        'can_create' => $canCreateUsers,
        'can_delete' => $canDeleteUsers,
        'can_manage' => $canManageUsers,
    ]
]);
```

---

## API Endpoints

Currently, there are no dedicated API endpoints for managing roles and permissions. However, you can manage them through:

1. **Database Seeders** (for initial setup)
2. **Tinker** (for testing)
3. **Custom Controllers** (if you create them)

### Example: Creating Custom Role/Permission Management Endpoints

If you want to create API endpoints for managing roles and permissions, here's an example:

```php
// In routes/api.php
Route::middleware(['auth:sanctum', 'admin', 'permission:roles.manage'])->group(function () {
    // Role management
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
    
    // Permission management
    Route::apiResource('permissions', PermissionController::class);
    Route::post('users/{user}/permissions', [UserController::class, 'assignPermissions']);
    Route::post('users/{user}/role', [UserController::class, 'assignRole']);
});
```

---

## Seeder Information

### RolePermissionSeeder

**Location**: `database/seeders/RolePermissionSeeder.php`

#### Predefined Permissions

The seeder creates the following permission groups:

##### User Management
- `users.view` - View admin users
- `users.create` - Create new admin users
- `users.update` - Update admin users
- `users.delete` - Delete admin users
- `users.ban` - Ban/unban admin users

##### Customer Management
- `customers.view` - View customers
- `customers.create` - Create customers
- `customers.update` - Update customers
- `customers.delete` - Delete customers

##### Product Management
- `products.view` - View products
- `products.create` - Create products
- `products.update` - Update products
- `products.delete` - Delete products

##### Order Management
- `orders.view` - View orders
- `orders.update` - Update orders
- `orders.delete` - Delete orders

##### Category Management
- `categories.view` - View categories
- `categories.create` - Create categories
- `categories.update` - Update categories
- `categories.delete` - Delete categories

##### Inventory Management
- `inventory.view` - View inventory
- `inventory.manage` - Manage inventory

##### Site Settings
- `settings.view` - View site settings
- `settings.update` - Update site settings

##### Roles & Permissions
- `roles.view` - View roles
- `roles.manage` - Manage roles and permissions

#### Predefined Roles

##### 1. Super Admin
- **Slug**: `super-admin`
- **Description**: Has all permissions
- **Permissions**: All permissions including role management

##### 2. Admin
- **Slug**: `admin`
- **Description**: Standard admin with most permissions
- **Permissions**: All permissions except role management (`roles.view`, `roles.manage`)

##### 3. Manager
- **Slug**: `manager`
- **Description**: Can view and update but not delete
- **Permissions**: All `.view` and `.update` permissions (no `.delete` or `.create`)

#### Running the Seeder

```bash
# Run only the role permission seeder
php artisan db:seed --class=RolePermissionSeeder

# Or include it in DatabaseSeeder and run all seeders
php artisan db:seed
```

---

## Best Practices

### 1. Permission Naming Convention

Use the format: `{resource}.{action}`

Examples:
- `users.view`
- `users.create`
- `users.update`
- `users.delete`
- `products.manage` (for multiple actions)

### 2. Permission Groups

Group related permissions using the `group` field:
- `users`
- `customers`
- `products`
- `orders`
- `categories`
- `inventory`
- `settings`
- `roles`

### 3. Route Protection

Always protect routes with appropriate middleware:

```php
// Good: Protected with authentication and permission
Route::middleware(['auth:sanctum', 'permission:users.create'])
    ->post('/users', [UserController::class, 'store']);

// Bad: No protection
Route::post('/users', [UserController::class, 'store']);
```

### 4. Controller Checks

Use middleware for route protection, but also check permissions in controllers for complex logic:

```php
public function destroy($id)
{
    // Middleware handles basic permission check
    // Controller handles business logic checks
    
    $user = User::find($id);
    
    // Additional business logic
    if ($user->id === auth()->id()) {
        return response()->json([
            'message' => 'You cannot delete yourself'
        ], 403);
    }
    
    $user->delete();
}
```

### 5. Admin Users

Admin users (with `role === 'admin'`) automatically have all permissions. This is handled in:
- `CheckPermission` middleware
- `User::hasPermission()` method

### 6. Role Assignment

Prefer assigning roles to users rather than individual permissions:

```php
// Good: Assign role
$user->role_id = $adminRole->id;
$user->save();

// Less ideal: Assign many individual permissions
$user->permissions()->attach([...many permission IDs...]);
```

### 7. Direct Permissions

Use direct permissions for exceptions or special cases:

```php
// User has Manager role (can view/update)
// But needs delete permission for specific case
$user->permissions()->attach($deletePermission->id);
```

### 8. Testing Permissions

Test permission checks in your application:

```php
// In tests
$user = User::factory()->create(['role' => 'admin']);
$role = Role::where('slug', 'manager')->first();
$user->role_id = $role->id;
$user->save();

$this->actingAs($user)
     ->get('/api/users')
     ->assertStatus(403); // Manager can't view users
```

---

## Migration Commands

### Run Migrations

```bash
# Run all migrations
php artisan migrate

# Run specific migration
php artisan migrate --path=/database/migrations/2025_11_16_091738_create_roles_table.php

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset
```

### Migration Files Created

1. `2025_11_16_091738_create_roles_table.php`
2. `2025_11_16_091739_create_permissions_table.php`
3. `2025_11_16_091740_create_permission_role_table.php`
4. `2025_11_16_091740_create_permission_user_table.php`
5. `2025_11_16_091758_add_role_id_to_users_table.php`

---

## Troubleshooting

### Issue: Permission check always returns false

**Solution**: 
- Ensure user has `role === 'admin'` OR
- Ensure user has `role_id` set and role has the permission OR
- Ensure user has the permission directly assigned

### Issue: Middleware not working

**Solution**:
- Check middleware is registered in `bootstrap/app.php`
- Verify route has correct middleware syntax: `permission:permission.slug`
- Ensure user is authenticated (`auth:sanctum`)

### Issue: Role not found

**Solution**:
- Run the seeder: `php artisan db:seed --class=RolePermissionSeeder`
- Check role exists: `Role::where('slug', 'admin')->first()`

### Issue: Permission not found

**Solution**:
- Run the seeder to create all permissions
- Check permission exists: `Permission::where('slug', 'users.view')->first()`

---

## Summary

The Role and Permission system provides:

✅ **Flexible Access Control**: Roles and direct permissions  
✅ **Easy Integration**: Simple middleware and model methods  
✅ **Admin Override**: Admin users have all permissions  
✅ **Granular Control**: Fine-grained permission checks  
✅ **Scalable**: Easy to add new permissions and roles  

For questions or issues, refer to the code in:
- Models: `app/Models/Role.php`, `app/Models/Permission.php`, `app/Models/User.php`
- Middleware: `app/Http/Middleware/CheckPermission.php`
- Seeder: `database/seeders/RolePermissionSeeder.php`

