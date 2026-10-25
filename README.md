# Digital Menu

A lightweight, multi-tenant digital menu and order-management service built with custom PHP MVC architecture, inspired by the structure of Nivad Store.

## Project status

Initial project scaffold. Business logic and production configuration are not implemented yet.

## Initial scope

- Step-by-step menu creation
- Public digital menu and QR access
- Product and category management
- Customer cart and order submission
- Business dashboard and order status management
- Subscription plans and verified online subscription payments
- Tenant data isolation

## Structure

- `app/` — core application layers
- `admin/` — business management areas
- `api/` — API endpoints
- `assets/` — CSS, JavaScript, images, and fonts
- `config/` — application configuration
- `pages/` — page templates
- `partials/` — shared view fragments
- `routes/` — route definitions
- `storage/` — logs and uploads
- `tests/` — tests

## Security requirements

Before production, implement authentication and authorization, tenant isolation, prepared SQL statements, CSRF protection, secure session settings, upload validation, secret management, and server-side payment verification.

## Development

See the README files in `config/`, `routes/`, `storage/`, and `tests/` for directory-specific notes.