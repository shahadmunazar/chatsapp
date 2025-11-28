# Admin Panel Setup Guide

This document provides information about the newly integrated role-based admin panel using the DattaAble template.

## Features

✅ **Role-Based Access Control**
- Admin role - Full access to all admin features
- Moderator role - Access to content management
- User role - Regular user access

✅ **Admin Dashboard**
- User statistics
- Post statistics
- Comment statistics
- Recent users overview
- Recent posts overview

✅ **User Management**
- List all users with search and filtering
- Create new users with role assignment
- Edit user details and roles
- View detailed user profiles
- Delete users (except yourself)

✅ **Post Management**
- List all posts with search
- View post details with comments
- Edit post content and images
- Delete posts with image cleanup

## Access Credentials

### Admin Account
- **Email:** admin@example.com
- **Password:** password

### Moderator Account
- **Email:** moderator@example.com
- **Password:** password

## Routes

All admin routes are protected by authentication, email verification, and role middleware:

```
/admin - Admin Dashboard
/admin/users - User Management
/admin/posts - Post Management
```

## Middleware

The admin panel uses the following middleware:
- `auth` - Requires authentication
- `verified` - Requires email verification
- `role:admin,moderator` - Requires admin or moderator role

## Database Changes

### New Migration
- `2025_11_26_103257_add_role_to_users_table.php` - Adds role column to users table

### User Roles
- `admin` - Administrator with full access
- `moderator` - Content moderator
- `user` - Regular user (default)

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── UserController.php
│   │       └── PostController.php
│   └── Middleware/
│       └── EnsureUserHasRole.php
├── Models/
│   └── User.php (updated with role methods)
└── UserRole.php (enum)

resources/views/
└── admin/
    ├── layouts/
    │   ├── app.blade.php
    │   ├── sidebar.blade.php
    │   └── header.blade.php
    ├── users/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   └── show.blade.php
    ├── posts/
    │   ├── index.blade.php
    │   └── show.blade.php
    └── dashboard.blade.php

public/admin/ (DattaAble assets)
├── css/
├── js/
├── fonts/
├── images/
└── json/
```

## User Model Methods

The User model now includes role checking methods:

```php
$user->hasRole(UserRole::Admin);  // Check specific role
$user->isAdmin();                 // Check if admin
$user->isModerator();             // Check if moderator
$user->isStaff();                 // Check if admin or moderator
```

## Testing

To create test users with specific roles:

```php
// Create admin
User::factory()->admin()->create();

// Create moderator
User::factory()->moderator()->create();

// Create regular user
User::factory()->create();
```

## Seeding

Run the admin seeder to create default admin and moderator accounts:

```bash
php artisan db:seed --class=AdminSeeder
```

## Security

- Admin routes are protected by role-based middleware
- Users cannot delete their own accounts
- Password changes are properly hashed
- All forms include CSRF protection
- Role changes are restricted to authorized users

## UI Features

- Responsive design (mobile-friendly)
- Dark/Light mode toggle
- Beautiful statistics cards
- Data tables with pagination
- Search and filtering
- User avatars
- Badge indicators for roles and status

## Next Steps

1. Customize the admin dashboard to show more relevant statistics
2. Add more admin features (e.g., comment management, settings)
3. Implement activity logs
4. Add export functionality for reports
5. Create custom admin notifications

## Support

For issues or questions about the admin panel, please refer to:
- DattaAble Template Documentation
- Laravel Documentation for role-based authorization

