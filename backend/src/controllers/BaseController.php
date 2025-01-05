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

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

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

    protected function beginTransaction() {
        $this->db->beginTransaction();
    }

    protected function commit() {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }

    protected function rollback() {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    protected function executeQuery($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    protected function fetchAll($query, $params = []) {
        return $this->executeQuery($query, $params)->fetchAll(PDO::FETCH_OBJ);
    }

    protected function fetch($query, $params = []) {
        return $this->executeQuery($query, $params)->fetch(PDO::FETCH_OBJ);
    }

    protected function fetchColumn($query, $params = []) {
        return $this->executeQuery($query, $params)->fetchColumn();
    }

    protected function lastInsertId() {
        return $this->db->lastInsertId();
    }

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