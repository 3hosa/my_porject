# TODO: Fix and Reorganize Project

## Database Fixes

- [ ] Update install.php to create users table with id, username, password, email, role, created_at
- [ ] Insert default admin user in install.php

## File Reorganization

- [ ] Move admin.php to admin/ folder
- [ ] Move addpro.php to admin/ folder
- [ ] Update header.php links to admin/admin.php and admin/addpro.php

## New Files

- [ ] Create profile.php for user profile page

## Access Restrictions

- [ ] Update register.php to set role to 'user'
- [ ] Ensure admin.php and admin/addpro.php check for admin role
- [ ] Restrict checkout.php to logged-in users
- [ ] Restrict cart.php to logged-in users for checkout

## Testing

- [ ] Test database installation
- [ ] Test user registration and login
- [ ] Test admin access
- [ ] Test purchase flow
