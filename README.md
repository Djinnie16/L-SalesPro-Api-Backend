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

APP_NAME=L-SalesPro
APP_ENV=local
APP_KEY=base64:Z3W8Q77SAxrqjF2jvdT93QK+3WvrO7JclAjORU+LuDo=
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

# PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=l_salespro
DB_USERNAME=laravel
DB_PASSWORD=strongpassword

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="L-SalesPro"

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000
SANCTUM_TOKEN_PREFIX=

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DOMAIN=localhost

VITE_APP_NAME="L-SalesPro"


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
POST            api/v1/auth/login ...................... AuthController@login
  POST            api/v1/auth/logout .................... AuthController@logout
  POST            api/v1/auth/password/forgot ... AuthController@forgotPassword
  POST            api/v1/auth/password/reset ..... AuthController@resetPassword
  POST            api/v1/auth/refresh .................. AuthController@refresh
  GET|HEAD        api/v1/auth/user ........................ AuthController@user
  GET|HEAD        api/v1/customers customers.index › Api\V1\CustomerController…
  POST            api/v1/customers customers.store › Api\V1\CustomerController…
  GET|HEAD        api/v1/customers/map-data . Api\V1\CustomerController@mapData
  GET|HEAD        api/v1/customers/{customer} customers.show › Api\V1\Customer…
  PUT|PATCH       api/v1/customers/{customer} customers.update › Api\V1\Custom…
  DELETE          api/v1/customers/{customer} customers.destroy › Api\V1\Custo…
  GET|HEAD        api/v1/customers/{id}/credit-status Api\V1\CustomerControlle…
  GET|HEAD        api/v1/customers/{id}/orders Api\V1\CustomerController@orders
  GET|HEAD        api/v1/orders .................. Api\V1\OrderController@index
  POST            api/v1/orders .................. Api\V1\OrderController@store
  POST            api/v1/orders/calculate-total Api\V1\OrderController@calcula…
  GET|HEAD        api/v1/orders/{id} .............. Api\V1\OrderController@show
  GET|HEAD        api/v1/orders/{id}/invoice Api\V1\OrderController@generateIn…
  PUT             api/v1/orders/{id}/status Api\V1\OrderController@updateStatus
  GET|HEAD        api/v1/stock-transfers stock-transfers.index › Api\V1\StockT…
  POST            api/v1/stock-transfers stock-transfers.store › Api\V1\StockT…
  POST            api/v1/stock-transfers/{id}/approve Api\V1\StockTransferCont…
  GET|HEAD        api/v1/stock-transfers/{stock_transfer} stock-transfers.show…
  GET|HEAD        api/v1/warehouses warehouses.index › Api\V1\WarehouseControl…
  POST            api/v1/warehouses warehouses.store › Api\V1\WarehouseControl…
  GET|HEAD        api/v1/warehouses/capacity-alerts Api\V1\WarehouseController…
  GET|HEAD        api/v1/warehouses/{id}/inventory Api\V1\WarehouseController@…
  GET|HEAD        api/v1/warehouses/{warehouse} warehouses.show › Api\V1\Wareh…
  PUT|PATCH       api/v1/warehouses/{warehouse} warehouses.update › Api\V1\War…
  DELETE          api/v1/warehouses/{warehouse} warehouses.destroy › Api\V1\Wa…
  GET|HEAD        sanctum/csrf-cookie sanctum.csrf-cookie › Laravel\Sanctum › …
  GET|HEAD        storage/{path} ................................ storage.local
  GET|HEAD        up .......................................................... 


Login:
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "david.kariuki@leysco.co.ke",
    "password": "SecurePass123!"
}

Order creation Example
POST /api/v1/orders
{
    "customer_id": 1,
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "unit_price": 4500.00
        }
    ],
    "discount_type": "percentage",
    "discount_value": 10
}

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
