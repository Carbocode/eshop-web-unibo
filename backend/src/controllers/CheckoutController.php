<?php
namespace App\Controllers;
use App\Utils\ApiResponse;
use Exception;

/**
 * CheckoutController handles the order checkout process.
 * Manages the conversion of cart items into orders, including
 * stock verification, order creation, and cart cleanup.
 */
class CheckoutController extends BaseController {
    /**
     * Processes the checkout of items in the customer's cart.
     * 
     * This method performs several operations in a transaction:
     * 1. Verifies cart is not empty
     * 2. Checks stock availability for all items
     * 3. Creates a new order
     * 4. Creates order items
     * 5. Updates product stock quantities
     * 6. Clears the customer's cart
     * 
     * @throws Exception When:
     *  - Cart is empty (400)
     *  - Insufficient stock for any item (400)
     *  - Authentication fails
     *  - Database operations fail
     * @return void
     */
    public function processCheckout() {
        $this->authenticate('customer');
        $data = ApiResponse::getRequestData();
        ApiResponse::validateRequest(['address_id'], $data);
        
        try {
            $this->beginTransaction();
            
            // Get cart items
            $cartItems = $this->fetchAll(
                "SELECT ci.*, t.price, t.stock_quantity 
                FROM cart_items ci
                JOIN tshirts t ON ci.tshirt_id = t.tshirt_id
                WHERE ci.customer_id = ?",
                [$this->userId]
            );
            
            if (empty($cartItems)) {
                throw new Exception("Cart is empty", 400);
            }
            
            // Calculate total and verify stock
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                if ($item['stock_quantity'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for item: " . $item['tshirt_id'], 400);
                }
                $totalAmount += $item['price'] * $item['quantity'];
            }
            
            // Create order
            $this->executeQuery(
                "INSERT INTO orders (customer_id, address_id, order_status, total_amount) 
                VALUES (?, ?, 'pending', ?)",
                [$this->userId, $data['address_id'], $totalAmount]
            );
            
            $orderId = $this->lastInsertId();
            
            // Create order items and update stock
            foreach ($cartItems as $item) {
                // Create order item
                $this->executeQuery(
                    "INSERT INTO order_items (order_id, tshirt_id, quantity, unit_price) 
                    VALUES (?, ?, ?, ?)",
                    [
                        $orderId,
                        $item['tshirt_id'],
                        $item['quantity'],
                        $item['price']
                    ]
                );
                
                // Update stock
                $this->executeQuery(
                    "UPDATE tshirts 
                    SET stock_quantity = stock_quantity - ? 
                    WHERE tshirt_id = ?",
                    [$item['quantity'], $item['tshirt_id']]
                );
            }
            
            // Clear cart
            $this->executeQuery(
                "DELETE FROM cart_items WHERE customer_id = ?",
                [$this->userId]
            );
            
            $this->commit();
            
            ApiResponse::success([
                'order_id' => $orderId
            ], 'Order created successfully');
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Processes incoming HTTP requests and routes them to appropriate handlers.
     * Currently only handles the POST /checkout endpoint for processing orders.
     * 
     * @return void
     */
    public function processRequest() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        $handlers = [
            '/checkout' => [
                'POST' => [$this, 'processCheckout']
            ]
        ];

        if (!isset($handlers[$uri][$method])) {
            ApiResponse::error('Not found', 404);
        }

        $this->handleRequest($method, $handlers[$uri]);
    }
}