# API Documentation

## Core Classes

### BaseController
The foundation class for all controllers in the system. It provides essential database operations and request handling functionality.

```php
abstract class BaseController {
    protected $db;        // Database connection
    protected $userId;    // Current authenticated user's ID
    protected $userType;  // Current user's type (customer/admin)
}
```

#### Methods

##### `__construct()`
Creates a new controller instance and establishes database connection.
```php
public function __construct()
```

##### `authenticate($requiredType = null)`
Verifies user authentication and sets up user context.
```php
protected function authenticate($requiredType = null)
```
- **Parameters:**
  - `$requiredType`: (optional) The required user type (e.g., 'customer', 'admin')
- **Returns:** User object with ID and type
- **Throws:** Exception if authentication fails

##### `executeQuery($query, $params = [])`
Executes a SQL query with optional parameters.
```php
protected function executeQuery($query, $params = [])
```
- **Parameters:**
  - `$query`: SQL query string
  - `$params`: Array of parameters to bind
- **Returns:** PDOStatement
- **Example:**
```php
$result = $this->executeQuery(
    "SELECT * FROM users WHERE id = ?", 
    [$userId]
);
```

##### `fetchAll($query, $params = [])`
Fetches all rows from a SQL query.
```php
protected function fetchAll($query, $params = [])
```
- **Parameters:**
  - `$query`: SQL query string
  - `$params`: Array of parameters
- **Returns:** Array of objects
- **Example:**
```php
$users = $this->fetchAll("SELECT * FROM users");
```

##### `fetch($query, $params = [])`
Fetches a single row from a SQL query.
```php
protected function fetch($query, $params = [])
```
- **Parameters:**
  - `$query`: SQL query string
  - `$params`: Array of parameters
- **Returns:** Object or false
- **Example:**
```php
$user = $this->fetch(
    "SELECT * FROM users WHERE id = ?", 
    [$id]
);
```

##### `fetchColumn($query, $params = [])`
Fetches a single value from the first column.
```php
protected function fetchColumn($query, $params = [])
```
- **Parameters:**
  - `$query`: SQL query string
  - `$params`: Array of parameters
- **Returns:** Mixed value
- **Example:**
```php
$count = $this->fetchColumn(
    "SELECT COUNT(*) FROM users"
);
```

##### Transaction Methods
```php
protected function beginTransaction()  // Start transaction
protected function commit()           // Commit transaction
protected function rollback()         // Rollback transaction
```

### Auth Middleware
Handles authentication and authorization using JWT tokens.

```php
class Auth {
    private static $jwt_secret;  // JWT signing key
}
```

#### Methods

##### `init()`
Initializes the JWT secret from environment variables.
```php
public static function init()
```

##### `authenticateUser($requiredType = null)`
Validates JWT token and user type.
```php
public static function authenticateUser($requiredType = null)
```
- **Parameters:**
  - `$requiredType`: (optional) Required user type
- **Returns:** Decoded JWT payload
- **Throws:** Exception if token is invalid
- **Example:**
```php
try {
    $user = Auth::authenticateUser('customer');
} catch (Exception $e) {
    // Handle authentication error
}
```

##### `generateToken($userId, $type, $role = null, $expHours = 24)`
Generates a new JWT token.
```php
public static function generateToken($userId, $type, $role = null, $expHours = 24)
```
- **Parameters:**
  - `$userId`: User's ID
  - `$type`: User type (customer/admin)
  - `$role`: (optional) User role
  - `$expHours`: Token expiration in hours
- **Returns:** JWT token string
- **Example:**
```php
$token = Auth::generateToken(123, 'customer', null, 24);
```

##### Password Handling
```php
public static function hashPassword($password)    // Hash a password
public static function verifyPassword($password, $hash)  // Verify password
```

### ApiResponse
Handles standardized API responses.

#### Methods

##### `success($data = null, $message = '', $code = 200)`
Sends successful response.
```php
public static function success($data = null, $message = '', $code = 200)
```
- **Parameters:**
  - `$data`: Response data
  - `$message`: Success message
  - `$code`: HTTP status code
- **Example:**
```php
ApiResponse::success(['user' => $user], 'Login successful');
```

##### `error($message, $code = 400)`
Sends error response.
```php
public static function error($message, $code = 400)
```
- **Parameters:**
  - `$message`: Error message
  - `$code`: HTTP status code
- **Example:**
```php
ApiResponse::error('Invalid credentials', 401);
```

## Creating Your Own API

### 1. Create a Controller
```php
class ProductController extends BaseController {
    public function getProduct() {
        // Authenticate user if needed
        $this->authenticate();
        
        // Get request data
        $data = ApiResponse::getRequestData();
        
        // Validate required fields
        ApiResponse::validateRequest(['id'], $data);
        
        try {
            // Fetch product from database
            $product = $this->fetch(
                "SELECT * FROM products WHERE id = ?",
                [$data['id']]
            );
            
            if (!$product) {
                ApiResponse::error('Product not found', 404);
            }
            
            // Return success response
            ApiResponse::success(['product' => $product]);
            
        } catch (Exception $e) {
            ApiResponse::error($e->getMessage(), 500);
        }
    }
}
```

### 2. Define Routes
```php
public function processRequest() {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    $handlers = [
        '/products/get' => [
            'GET' => [$this, 'getProduct']
        ]
    ];

    if (!isset($handlers[$uri][$method])) {
        ApiResponse::error('Not found', 404);
    }

    $this->handleRequest($method, $handlers[$uri]);
}
```

### 3. Handle Database Operations
```php
// Fetch multiple records
$products = $this->fetchAll(
    "SELECT * FROM products WHERE category = ?",
    [$category]
);

// Insert record
$this->executeQuery(
    "INSERT INTO products (name, price) VALUES (?, ?)",
    [$name, $price]
);

// Update with transaction
try {
    $this->beginTransaction();
    
    $this->executeQuery(
        "UPDATE products SET stock = stock - ? WHERE id = ?",
        [$quantity, $productId]
    );
    
    $this->executeQuery(
        "INSERT INTO orders (product_id, quantity) VALUES (?, ?)",
        [$productId, $quantity]
    );
    
    $this->commit();
} catch (Exception $e) {
    $this->rollback();
    throw $e;
}
```

### 4. Implement Authentication
```php
// Protect endpoint
public function updateProduct() {
    // Require admin authentication
    $this->authenticate('admin');
    
    // Rest of your code...
}

// Generate token on login
$token = Auth::generateToken($userId, 'customer');
ApiResponse::success(['token' => $token]);
```

### Best Practices

1. **Always Validate Input**
```php
ApiResponse::validateRequest(['email', 'password'], $data);
```

2. **Use Prepared Statements**
```php
// Good
$user = $this->fetch("SELECT * FROM users WHERE id = ?", [$id]);

// Bad - SQL injection risk
$user = $this->fetch("SELECT * FROM users WHERE id = " . $id);
```

3. **Handle Errors Properly**
```php
try {
    // Your code
} catch (Exception $e) {
    ApiResponse::error($e->getMessage(), 500);
}
```

4. **Use Transactions for Multiple Operations**
```php
try {
    $this->beginTransaction();
    // Multiple database operations
    $this->commit();
} catch (Exception $e) {
    $this->rollback();
    throw $e;
}
```

5. **Consistent Response Format**
```php
// Success response
{
    "status": "success",
    "data": { ... },
    "message": "Operation successful"
}

// Error response
{
    "status": "error",
    "message": "Error description"
}
```

6. **Proper HTTP Status Codes**
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Server Error