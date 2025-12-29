# L-SalesPro API Backend

## 📋 Project Overview
L-SalesPro is a comprehensive sales automation system backend API built with Laravel 12. This RESTful API powers inventory management, sales transactions, customer relationships, and analytics for Leysco Limited.

**Confidential**: LEYSCO-LARAVEL-2025-06

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer 2.5 or higher
- MySQL 8.0+ or
- Redis 7.0+
- Node.js 18+ (for asset compilation if needed)

### Installation

1. Clone the repository
git clone <repository-url>
cd l-salespro-api

2. Install dependecies
composer install

3. Configure environment
cp .env.example .env
php artisan key:generate

4. Run migrations and seeders
php artisan migrate
php artisan db:seed

5. Start development server
php artisan serve

PROJECT ARCHITECTURE
app/
├── Helpers/           # Custom helper classes
├── Http/
│   ├── Controllers/   # API controllers
│   ├── Middleware/    # Custom middleware
│   └── Requests/      # Form request validation
├── Models/            # Eloquent models
├── Providers/         # Service providers
├── Repositories/      # Repository pattern classes
├── Services/          # Business logic services
└── Resources/         # API resource classes

🔐 Authentication & Authorization
Authentication Flow

    POST /api/v1/auth/login with credentials

    Receive Sanctum token in response

    Include token in Authorization: Bearer <token> header

    Token automatically refreshes on valid requests

    POST /api/v1/auth/logout to invalidate token

Role-Based Access Control (RBAC)

    Sales Manager: Full system access

    Sales Representative: Limited to own sales and view inventory

Permission System

Permissions are stored as JSON array in users table:

    view_all_sales - View all sales orders

    view_own_sales - View only own sales

    create_sales - Create new sales orders

    approve_sales - Approve/confirm orders

    manage_inventory - Manage products and stock

    view_inventory - View inventory only

📊 API Modules
1. Authentication Module

    POST /api/v1/auth/login - User authentication

    POST /api/v1/auth/logout - User logout

    POST /api/v1/auth/refresh - Token refresh

    GET /api/v1/auth/user - Current user profile

    POST /api/v1/auth/password/forgot - Password reset request

    POST /api/v1/auth/password/reset - Password reset confirmation

2. Dashboard Analytics Module

    GET /api/v1/dashboard/summary - Overall sales metrics

    GET /api/v1/dashboard/sales-performance - Sales data with date filtering

    GET /api/v1/dashboard/inventory-status - Category-wise inventory summary

    GET /api/v1/dashboard/top-products - Top 5 selling products

3. Inventory Management Module

    GET /api/v1/products - List products with pagination

    GET /api/v1/products/{id} - Product details

    POST /api/v1/products - Create product (Admin only)

    PUT /api/v1/products/{id} - Update product (Admin only)

    DELETE /api/v1/products/{id} - Soft delete product

    GET /api/v1/products/{id}/stock - Real-time stock across warehouses

    POST /api/v1/products/{id}/reserve - Reserve stock for order

    POST /api/v1/products/{id}/release - Release reserved stock

    GET /api/v1/products/low-stock - Products below reorder level

4. Sales Order Management Module

    GET /api/v1/orders - List orders with filters

    GET /api/v1/orders/{id} - Order details

    POST /api/v1/orders - Create new order

    PUT /api/v1/orders/{id}/status - Update order status

    GET /api/v1/orders/{id}/invoice - Generate invoice data

    POST /api/v1/orders/calculate-total - Preview order calculations

5. Customer Management Module

    GET /api/v1/customers - List customers with pagination

    GET /api/v1/customers/{id} - Customer details

    POST /api/v1/customers - Create customer

    PUT /api/v1/customers/{id} - Update customer

    DELETE /api/v1/customers/{id} - Soft delete

    GET /api/v1/customers/{id}/orders - Customer order history

    GET /api/v1/customers/{id}/credit-status - Credit limit and balance

    GET /api/v1/customers/map-data - Customer locations for mapping

6. Warehouse Management Module

    GET /api/v1/warehouses - List all warehouses

    GET /api/v1/warehouses/{id}/inventory - Warehouse-specific inventory

    POST /api/v1/stock-transfers - Transfer stock between warehouses

    GET /api/v1/stock-transfers - Transfer history

7. Notifications Module

    GET /api/v1/notifications - User notifications with pagination

    PUT /api/v1/notifications/{id}/read - Mark as read

    PUT /api/v1/notifications/read-all - Mark all as read

    DELETE /api/v1/notifications/{id} - Delete notification

    GET /api/v1/notifications/unread-count - Unread count

🔧 Technical Implementation
API Standards

    RESTful Design: Resource-based URLs, proper HTTP verbs

    Versioning: All endpoints under /api/v1/

    Response Format: Consistent JSON structure

    Status Codes: Appropriate HTTP response codes

    Pagination: Standardized pagination metadata


Response Format
{
  "success": true,
  "message": "Operation successful",
  "data": { /* response data */ },
  "meta": { /* pagination metadata */ },
  "errors": null
}

Error Format
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}

Performance Optimization

    Redis Caching: Dashboard analytics, product listings, user sessions

    Eager Loading: Prevents N+1 query problems

    Database Indexing: Optimized query performance

    Queue System: Async processing for emails and notifications

Security Measures

    Laravel Sanctum: Token-based authentication

    Input Validation: Form request validation

    SQL Injection Prevention: Eloquent ORM with parameter binding

    Rate Limiting: Throttled authentication endpoints

    CORS Configuration: Controlled API access

    Password Requirements: Minimum 8 chars with uppercase, number, special character

Custom Components
Helpers (App\Helpers\LeysHelpers)

    formatCurrency($amount) - Format: "KES 10,000.00 /="

    generateOrderNumber() - Format: "ORD-YYYY-MM-XXX"

    calculateTax($amount, $rate) - Tax calculation helper

Middleware

    CheckCreditLimit - Validates customer credit before order creation

    LogApiActivity - Creates audit trail for all API requests

    CheckRole - Role-based access control

    CheckPermission - Permission-based access control

Naming Conventions

    Custom config files: leys_ prefix (e.g., leys_sales.php)

    Custom service classes: Leys prefix (e.g., LeysOrderService)


TEST SETUP
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Generate test coverage report
php artisan test --coverage --min=60

Test Data

Test data is seeded using the provided JSON structures:

    users.json - System users

    products.json - Product catalog

    customers.json - Customer data

    warehouses.json - Warehouse information
