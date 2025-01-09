# Soccer T-Shirt Shop - Technical Documentation

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Database Schema](#database-schema)
3. [Authentication & Authorization](#authentication--authorization)
4. [API Endpoints](#api-endpoints)
5. [Environment Setup](#environment-setup)
6. [Dependencies](#dependencies)
7. [Security](#security)
8. [Error Handling](#error-handling)
9. [Performance & Scalability](#performance--scalability)
10. [Development Guidelines](#development-guidelines)
11. [Deployment](#deployment)
12. [Maintenance](#maintenance)

## Architecture Overview

The system follows a modern PHP-based REST API architecture with the following key components:

- **Frontend**: Multi-page application built with vanilla JavaScript and SCSS
- **Backend**: PHP 8.3 RESTful API
- **Database**: MySQL database for persistent storage
- **Authentication**: JWT-based authentication system
- **File Storage**: Local storage for images with URL references

### System Components Diagram
```
┌─────────────┐         ┌─────────────┐         ┌─────────────┐
│   Frontend  │ ───────▶│   Backend   │ ───────▶│  Database   │
│  (JS/SCSS)  │◀─────── │  (PHP API)  │◀─────── │   (MySQL)   │
└─────────────┘         └─────────────┘         └─────────────┘
```

## Database Schema

The database consists of the following main entities:

### Core Tables

#### Teams
```sql
CREATE TABLE teams (
    team_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    type ENUM('national', 'club') NOT NULL,
    country VARCHAR(100) NOT NULL
);
```

#### T-Shirts
```sql
CREATE TABLE tshirts (
    tshirt_id INT PRIMARY KEY AUTO_INCREMENT,
    team_id INT NOT NULL,
    edition_id INT NOT NULL,
    size ENUM('XS', 'S', 'M', 'L', 'XL', 'XXL') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255) NOT NULL
);
```

### User Management Tables

#### Customers
```sql
CREATE TABLE customers (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20)
);
```

#### Admins
```sql
CREATE TABLE admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'super_admin') NOT NULL DEFAULT 'admin'
);
```

### Order Management Tables

#### Orders
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    address_id INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Authentication & Authorization

The system uses JWT (JSON Web Tokens) for authentication, implemented using the `firebase/php-jwt` library.

### Authentication Flow

1. **User Registration**:
   ```php
   POST /auth/register
   {
       "email": "user@example.com",
       "password": "secure_password",
       "firstName": "John",
       "lastName": "Doe",
       "phone": "+1234567890"
   }
   ```

2. **User Login**:
   ```php
   POST /auth/login
   {
       "email": "user@example.com",
       "password": "secure_password"
   }
   ```

3. **Token Generation**:
   ```php
   $token = Auth::generateToken($userId, 'customer', null, 24);
   // Returns JWT valid for 24 hours
   ```

### Authorization Levels

- **Customer**: Basic user access
- **Admin**: Management access
- **Super Admin**: Full system access

## API Endpoints

### Authentication Endpoints

```
POST /auth/register     - Register new customer
POST /auth/login        - Customer login
POST /auth/admin/login  - Admin login
```

### Protected Endpoints

All protected endpoints require the Authorization header:
```
Authorization: Bearer <jwt_token>
```

## Environment Setup

### Requirements

- PHP 8.3+
- MySQL 5.7+
- Composer
- Required PHP Extensions:
  - mysqli
  - curl
  - json
  - openssl

### Environment Variables

Create a `.env` file in the root directory:
```env
DB_USER=your_db_user
DB_PWD=your_db_password
DB_PORT=3306
JWT_SECRET=your_secure_jwt_secret
```

### Installation Steps

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Set up the database:
   ```bash
   mysql -u root -p < backend/DBScripts/DBCreate.sql
   ```
4. Configure environment variables
5. Start the development server:
   ```bash
   php -S localhost:8000 -t backend/
   ```

## Dependencies

### Production Dependencies
```json
{
    "php": "^8.3",
    "ext-mysqli": "*",
    "ext-curl": "*",
    "ext-json": "*",
    "ext-openssl": "*",
    "phpmailer/phpmailer": "^6.9",
    "vlucas/phpdotenv": "^5.6",
    "guzzlehttp/guzzle": "^7.9",
    "firebase/php-jwt": "^6.10"
}
```

### Development Dependencies
```json
{
    "friendsofphp/php-cs-fixer": "^3.65",
    "phpstan/phpstan": "^2.0"
}
```

## Security

### Password Handling
- Passwords are hashed using PHP's `password_hash()` with DEFAULT algorithm
- Password verification uses `password_verify()`
- No plain-text passwords are stored

### JWT Security
- Tokens expire after 24 hours (configurable)
- Uses HS256 algorithm for signing
- Requires secure JWT_SECRET environment variable

### Database Security
- PDO prepared statements prevent SQL injection
- Connection uses PDO::ERRMODE_EXCEPTION
- Sensitive credentials stored in environment variables

## Error Handling

The system uses a centralized error handling approach through the `ApiResponse` class:

```php
ApiResponse::error('Error message', 400);
ApiResponse::success($data, 'Success message', 200);
```

### HTTP Status Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Internal Server Error

## Performance & Scalability

### Database Optimization
- Proper indexing on frequently queried columns
- Foreign key constraints for data integrity
- Connection pooling through PDO

### Caching Considerations
- Implement Redis/Memcached for session storage
- Cache frequently accessed product data
- Implement API response caching

## Development Guidelines

### Code Style
- PSR-4 autoloading standard
- PHP CS Fixer configuration
- PHPStan Level 7 analysis

### Version Control
- Feature branch workflow
- Pull request reviews
- Semantic versioning

### Testing
- Unit tests for business logic
- Integration tests for API endpoints
- End-to-end testing for critical flows

## Deployment

### Production Requirements
- PHP 8.3+ production environment
- MySQL database server
- Web server (Apache/Nginx)
- SSL certificate for HTTPS

### Deployment Steps
1. Pull latest code from main branch
2. Install production dependencies
3. Run database migrations
4. Update environment variables
5. Clear caches
6. Reload web server

## Maintenance

### Monitoring
- Log API errors and exceptions
- Monitor database performance
- Track API response times

### Backup Procedures
- Daily database backups
- Secure backup storage
- Regular backup testing

### Updates
- Regular dependency updates
- Security patch application
- Performance optimization

## Troubleshooting

### Common Issues
1. Database Connection Failures
   - Check environment variables
   - Verify database server status
   - Check network connectivity

2. Authentication Issues
   - Verify JWT_SECRET configuration
   - Check token expiration
   - Validate request headers

3. API Errors
   - Check server logs
   - Verify request format
   - Validate input data

### Debug Mode
Enable debug mode in development:
```php
error_reporting(E_ALL);
ini_set('display_errors', '1');