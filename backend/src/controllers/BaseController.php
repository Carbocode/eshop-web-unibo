<?php

namespace App\Controllers;

use PDO;
use PDOException;
use Exception;
use App\Middleware\Auth;
use App\Config\Database;
use App\Utils\ApiResponse;

/**
 * BaseController is an abstract class that provides common functionality for all controllers.
 * It includes methods for database transactions, user authentication, and handling HTTP requests.
 *
 * @property PDO $db The database connection instance.
 * @property int $userId The authenticated user's ID.
 * @property string $userType The authenticated user's type.
 */
abstract class BaseController {
    protected $db;
    protected $userId;
    protected $userType;

    /**
     * Processes incoming HTTP requests and routes them to appropriate handlers.
     * Currently only handles GET requests to retrieve team data.
     * 
     * @return void
     */
    abstract protected function processRequest();

    /**
     * Constructor initializes the database connection.
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Authenticates the current user and sets user properties.
     *
     * @param string|null $requiredType Optional user type required for authentication
     * @return object User object containing userId and type
     * @throws Exception If authentication fails
     */
    protected function authenticate($requiredType = null) {
        try {
            $user = Auth::authenticateUser($requiredType);
            $this->userId = $user->userId;
            $this->userType = $user->type;
            return $user;
        } catch (Exception $e) {
            ApiResponse::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Begins a database transaction.
     */
    protected function beginTransaction() {
        $this->db->beginTransaction();
    }

    /**
     * Commits the current database transaction if one is active.
     */
    protected function commit() {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }

    /**
     * Rolls back the current database transaction if one is active.
     */
    protected function rollback() {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    /**
     * Executes a prepared SQL query with parameters.
     *
     * @param string $query The SQL query to execute
     * @param array $params Array of parameters to bind to the query
     * @return \PDOStatement The executed PDO statement
     * @throws Exception If the query fails
     */
    protected function executeQuery($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    /**
     * Fetches all rows from a query result as objects.
     *
     * @param string $query The SQL query to execute
     * @param array $params Array of parameters to bind to the query
     * @return array Array of objects representing the result rows
     */
    protected function fetchAll($query, $params = []) {
        return $this->executeQuery($query, $params)->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetches a single row from a query result as an object.
     *
     * @param string $query The SQL query to execute
     * @param array $params Array of parameters to bind to the query
     * @return object|false Object representing the result row or false if no row found
     */
    protected function fetch($query, $params = []) {
        return $this->executeQuery($query, $params)->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Fetches a single column from a query result.
     *
     * @param string $query The SQL query to execute
     * @param array $params Array of parameters to bind to the query
     * @return mixed The value of the column
     */
    protected function fetchColumn($query, $params = []) {
        return $this->executeQuery($query, $params)->fetchColumn();
    }

    /**
     * Gets the ID of the last inserted row.
     *
     * @return string The last insert ID
     */
    protected function lastInsertId() {
        return $this->db->lastInsertId();
    }

    /**
     * Handles HTTP requests by routing to appropriate handlers based on method.
     * Usually called by processRequest
     *
     * @param string $method The HTTP method (GET, POST, etc.)
     * @param array $handlers Array of handler functions keyed by HTTP method
     * @throws Exception If handler execution fails
     */
    protected function handleRequest($method, $handlers) {
        if (!isset($handlers[$method])) {
            ApiResponse::error('Method not allowed', 405);
        }

        try {
            $handlers[$method]();
        } catch (Exception $e) {
            $this->rollback();
            ApiResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}