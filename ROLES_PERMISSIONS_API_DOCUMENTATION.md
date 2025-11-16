# Roles & Permissions API Documentation

## Base URL
All endpoints are prefixed with `/api`

## Authentication
All endpoints require:
- **Authentication**: `auth:sanctum` middleware
- **Admin Role**: User must have `role === 'admin'`
- **Permission**: User must have `roles.manage` permission (except for view endpoints)

---

## Roles API Endpoints

### 1. List All Roles
Get a paginated list of all roles.

**Endpoint**: `GET /api/roles`

**Query Parameters**:
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 10)
- `search` (optional): Search in name, slug, or description
- `is_active` (optional): Filter by active status (true/false)
- `sort_by` (optional): Sort field (default: created_at)
- `sort_order` (optional): Sort direction (asc/desc, default: desc)

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Super Admin",
      "slug": "super-admin",
      "description": "Has all permissions",
      "is_active": true,
      "created_at": "2025-11-16T09:00:00.000000Z",
      "updated_at": "2025-11-16T09:00:00.000000Z",
      "permissions": [...]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 3,
    "from": 1,
    "to": 3
  }
}
```

---

### 2. Get Single Role
Get details of a specific role with permissions and users.

**Endpoint**: `GET /api/roles/{id}`

**Response**:
```json
{
  "data": {
    "id": 1,
    "name": "Super Admin",
    "slug": "super-admin",
    "description": "Has all permissions",
    "is_active": true,
    "created_at": "2025-11-16T09:00:00.000000Z",
    "updated_at": "2025-11-16T09:00:00.000000Z",
    "permissions": [...],
    "users": [...]
  }
}
```

---

### 3. Create Role
Create a new role.

**Endpoint**: `POST /api/roles`

**Request Body**:
```json
{
  "name": "Content Manager",
  "slug": "content-manager",  // Optional, auto-generated from name if not provided
  "description": "Manages content and products",
  "is_active": true,  // Optional, default: true
  "permissions": [1, 2, 3]  // Optional, array of permission IDs
}
```

**Response** (201 Created):
```json
{
  "message": "Role created successfully",
  "data": {
    "id": 4,
    "name": "Content Manager",
    "slug": "content-manager",
    "description": "Manages content and products",
    "is_active": true,
    "permissions": [...]
  }
}
```

---

### 4. Update Role
Update an existing role.

**Endpoint**: `PUT /api/roles/{id}` or `PATCH /api/roles/{id}`

**Request Body** (all fields optional):
```json
{
  "name": "Updated Role Name",
  "slug": "updated-role-slug",
  "description": "Updated description",
  "is_active": false,
  "permissions": [1, 2, 3, 4]  // Updates all permissions
}
```

**Response**:
```json
{
  "message": "Role updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Role Name",
    ...
  }
}
```

---

### 5. Delete Role
Delete a role. Cannot delete if role is assigned to users.

**Endpoint**: `DELETE /api/roles/{id}`

**Response** (200 OK):
```json
{
  "message": "Role deleted successfully"
}
```

**Error Response** (422 Unprocessable Entity):
```json
{
  "message": "Cannot delete role. It is assigned to 5 user(s).",
  "users_count": 5
}
```

---

### 6. Assign Permissions to Role
Assign permissions to a role (replaces existing permissions).

**Endpoint**: `POST /api/roles/{id}/permissions`

**Request Body**:
```json
{
  "permissions": [1, 2, 3, 4, 5]  // Array of permission IDs
}
```

**Response**:
```json
{
  "message": "Permissions assigned successfully",
  "data": {
    "id": 1,
    "name": "Admin",
    "permissions": [...]
  }
}
```

---

### 7. Remove Permissions from Role
Remove specific permissions from a role.

**Endpoint**: `DELETE /api/roles/{id}/permissions`

**Request Body**:
```json
{
  "permissions": [1, 2]  // Array of permission IDs to remove
}
```

**Response**:
```json
{
  "message": "Permissions removed successfully",
  "data": {
    "id": 1,
    "name": "Admin",
    "permissions": [...]
  }
}
```

---

### 8. Get Role Permissions
Get all permissions assigned to a role.

**Endpoint**: `GET /api/roles/{id}/permissions`

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "View Users",
      "slug": "users.view",
      "group": "users",
      ...
    }
  ]
}
```

---

### 9. Get Role Users
Get all users assigned to a role.

**Endpoint**: `GET /api/roles/{id}/users`

**Query Parameters**:
- `page` (optional): Page number
- `per_page` (optional): Items per page

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      ...
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 5
  }
}
```

---

### 10. Toggle Role Active Status
Toggle the active status of a role.

**Endpoint**: `POST /api/roles/{id}/toggle-active`

**Response**:
```json
{
  "message": "Role status updated successfully",
  "data": {
    "id": 1,
    "name": "Admin",
    "is_active": false,
    ...
  }
}
```

---

## Permissions API Endpoints

### 1. List All Permissions
Get a paginated list of all permissions.

**Endpoint**: `GET /api/permissions`

**Query Parameters**:
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 50)
- `search` (optional): Search in name, slug, or description
- `group` (optional): Filter by permission group
- `is_active` (optional): Filter by active status (true/false)
- `sort_by` (optional): Sort field (default: group)
- `sort_order` (optional): Sort direction (asc/desc, default: asc)

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "View Users",
      "slug": "users.view",
      "description": "View admin users",
      "group": "users",
      "is_active": true,
      "created_at": "2025-11-16T09:00:00.000000Z",
      "updated_at": "2025-11-16T09:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 50,
    "total": 30,
    "from": 1,
    "to": 30
  }
}
```

---

### 2. Get Grouped Permissions
Get all permissions grouped by their group.

**Endpoint**: `GET /api/permissions/grouped`

**Response**:
```json
{
  "data": {
    "users": [
      {
        "id": 1,
        "name": "View Users",
        "slug": "users.view",
        ...
      },
      ...
    ],
    "products": [
      {
        "id": 5,
        "name": "View Products",
        "slug": "products.view",
        ...
      },
      ...
    ]
  }
}
```

---

### 3. Get Permission Groups
Get a list of all permission groups.

**Endpoint**: `GET /api/permissions/groups`

**Response**:
```json
{
  "data": [
    "users",
    "customers",
    "products",
    "orders",
    "categories",
    "inventory",
    "settings",
    "roles"
  ]
}
```

---

### 4. Get Single Permission
Get details of a specific permission with roles and users.

**Endpoint**: `GET /api/permissions/{id}`

**Response**:
```json
{
  "data": {
    "id": 1,
    "name": "View Users",
    "slug": "users.view",
    "description": "View admin users",
    "group": "users",
    "is_active": true,
    "created_at": "2025-11-16T09:00:00.000000Z",
    "updated_at": "2025-11-16T09:00:00.000000Z",
    "roles": [...],
    "users": [...]
  }
}
```

---

### 5. Create Permission
Create a new permission.

**Endpoint**: `POST /api/permissions`

**Request Body**:
```json
{
  "name": "Manage Reports",
  "slug": "reports.manage",  // Optional, auto-generated from name if not provided
  "description": "Can view and manage reports",
  "group": "reports",  // Optional
  "is_active": true  // Optional, default: true
}
```

**Response** (201 Created):
```json
{
  "message": "Permission created successfully",
  "data": {
    "id": 31,
    "name": "Manage Reports",
    "slug": "reports.manage",
    "description": "Can view and manage reports",
    "group": "reports",
    "is_active": true,
    ...
  }
}
```

---

### 6. Update Permission
Update an existing permission.

**Endpoint**: `PUT /api/permissions/{id}` or `PATCH /api/permissions/{id}`

**Request Body** (all fields optional):
```json
{
  "name": "Updated Permission Name",
  "slug": "updated.permission.slug",
  "description": "Updated description",
  "group": "updated-group",
  "is_active": false
}
```

**Response**:
```json
{
  "message": "Permission updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Permission Name",
    ...
  }
}
```

---

### 7. Delete Permission
Delete a permission. Cannot delete if assigned to roles or users.

**Endpoint**: `DELETE /api/permissions/{id}`

**Response** (200 OK):
```json
{
  "message": "Permission deleted successfully"
}
```

**Error Response** (422 Unprocessable Entity):
```json
{
  "message": "Cannot delete permission. It is assigned to 3 role(s).",
  "roles_count": 3
}
```

or

```json
{
  "message": "Cannot delete permission. It is assigned to 2 user(s).",
  "users_count": 2
}
```

---

### 8. Toggle Permission Active Status
Toggle the active status of a permission.

**Endpoint**: `POST /api/permissions/{id}/toggle-active`

**Response**:
```json
{
  "message": "Permission status updated successfully",
  "data": {
    "id": 1,
    "name": "View Users",
    "is_active": false,
    ...
  }
}
```

---

## Complete API Endpoints List

### Roles Endpoints

| Method | Endpoint | Description | Permission Required |
|--------|----------|------------|-------------------|
| GET | `/api/roles` | List all roles | `roles.manage` |
| GET | `/api/roles/{id}` | Get single role | `roles.manage` |
| POST | `/api/roles` | Create role | `roles.manage` |
| PUT/PATCH | `/api/roles/{id}` | Update role | `roles.manage` |
| DELETE | `/api/roles/{id}` | Delete role | `roles.manage` |
| POST | `/api/roles/{id}/permissions` | Assign permissions | `roles.manage` |
| DELETE | `/api/roles/{id}/permissions` | Remove permissions | `roles.manage` |
| GET | `/api/roles/{id}/permissions` | Get role permissions | `roles.manage` |
| GET | `/api/roles/{id}/users` | Get role users | `roles.manage` |
| POST | `/api/roles/{id}/toggle-active` | Toggle role status | `roles.manage` |

### Permissions Endpoints

| Method | Endpoint | Description | Permission Required |
|--------|----------|------------|-------------------|
| GET | `/api/permissions` | List all permissions | `roles.manage` |
| GET | `/api/permissions/grouped` | Get grouped permissions | `roles.manage` |
| GET | `/api/permissions/groups` | Get permission groups | `roles.manage` |
| GET | `/api/permissions/{id}` | Get single permission | `roles.manage` |
| POST | `/api/permissions` | Create permission | `roles.manage` |
| PUT/PATCH | `/api/permissions/{id}` | Update permission | `roles.manage` |
| DELETE | `/api/permissions/{id}` | Delete permission | `roles.manage` |
| POST | `/api/permissions/{id}/toggle-active` | Toggle permission status | `roles.manage` |

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "Unauthorized. You do not have the required permission: roles.manage"
}
```

### 404 Not Found
```json
{
  "message": "No query results for model [App\\Models\\Role] {id}"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "slug": ["The slug has already been taken."]
  }
}
```

---

## Example Usage

### Create a Role with Permissions

```bash
curl -X POST http://localhost:8000/api/roles \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Content Manager",
    "description": "Manages content and products",
    "permissions": [5, 6, 7, 8]
  }'
```

### Assign Permissions to Role

```bash
curl -X POST http://localhost:8000/api/roles/2/permissions \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "permissions": [1, 2, 3, 4, 5]
  }'
```

### Get Grouped Permissions

```bash
curl -X GET http://localhost:8000/api/permissions/grouped \
  -H "Authorization: Bearer {token}"
```

### Create a Permission

```bash
curl -X POST http://localhost:8000/api/permissions \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Manage Reports",
    "slug": "reports.manage",
    "group": "reports",
    "description": "Can view and manage reports"
  }'
```

---

## Notes

1. **Auto-slug Generation**: If `slug` is not provided when creating a role or permission, it will be auto-generated from the `name` field using `Str::slug()`.

2. **Permission Assignment**: When assigning permissions to a role using `POST /api/roles/{id}/permissions`, it replaces all existing permissions. Use `DELETE /api/roles/{id}/permissions` to remove specific permissions.

3. **Cascade Protection**: 
   - Roles cannot be deleted if assigned to users
   - Permissions cannot be deleted if assigned to roles or users

4. **Active Status**: Inactive roles/permissions are still returned in listings but can be filtered using the `is_active` query parameter.

5. **Pagination**: All list endpoints support pagination with `page` and `per_page` query parameters.

6. **Search**: List endpoints support searching across relevant fields (name, slug, description).

7. **Sorting**: All list endpoints support custom sorting with `sort_by` and `sort_order` parameters.

