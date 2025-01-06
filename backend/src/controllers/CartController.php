<?php
namespace App\Controllers;
use App\Utils\ApiResponse;
use Exception;
class CartController extends BaseController {
    public function getCartItems() {
        $this->authenticate('customer');
        
        $items = $this->fetchAll(
            "SELECT ci.cart_item_id, ci.quantity, 
            t.tshirt_id, t.price, t.image_url, t.size,
            tm.name as team_name, e.name as edition_name
            FROM cart_items ci
            JOIN tshirts t ON ci.tshirt_id = t.tshirt_id
            JOIN teams tm ON t.team_id = tm.team_id
            JOIN editions e ON t.edition_id = e.edition_id
            WHERE ci.customer_id = ?",
            [$this->userId]
        );
        
        $total = array_reduce($items, function($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
        
        ApiResponse::success([
            'items' => $items,
            'total' => $total
        ]);
    }

    public function addToCart() {
        $this->authenticate('customer');
        $data = ApiResponse::getRequestData();
        ApiResponse::validateRequest(['tshirt_id', 'quantity'], $data);
        
        try {
            $this->beginTransaction();
            
            // Check stock availability
            $stock = $this->fetchColumn(
                "SELECT stock_quantity FROM tshirts WHERE tshirt_id = ?",
                [$data['tshirt_id']]
            );
            
            if ($stock < $data['quantity']) {
                throw new Exception('Insufficient stock', 400);
            }
            
            // Check if item exists in cart
            $existingItem = $this->fetch(
                "SELECT cart_item_id, quantity FROM cart_items 
                WHERE customer_id = ? AND tshirt_id = ?",
                [$this->userId, $data['tshirt_id']]
            );
            
            if ($existingItem) {
                $newQuantity = $existingItem['quantity'] + $data['quantity'];
                if ($newQuantity > $stock) {
                    throw new Exception('Insufficient stock for requested quantity', 400);
                }
                
                $this->executeQuery(
                    "UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?",
                    [$newQuantity, $existingItem['cart_item_id']]
                );
            } else {
                $this->executeQuery(
                    "INSERT INTO cart_items (customer_id, tshirt_id, quantity) VALUES (?, ?, ?)",
                    [$this->userId, $data['tshirt_id'], $data['quantity']]
                );
            }
            
            $this->commit();
            ApiResponse::success(null, 'Item added to cart successfully');
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function updateCartItem() {
        $this->authenticate('customer');
        $data = ApiResponse::getRequestData();
        ApiResponse::validateRequest(['cart_item_id', 'quantity'], $data);
        
        try {
            $this->beginTransaction();
            
            // Verify ownership and get tshirt info
            $item = $this->fetch(
                "SELECT t.tshirt_id, t.stock_quantity 
                FROM cart_items ci
                JOIN tshirts t ON ci.tshirt_id = t.tshirt_id
                WHERE ci.cart_item_id = ? AND ci.customer_id = ?",
                [$data['cart_item_id'], $this->userId]
            );
            
            if (!$item) {
                throw new Exception('Cart item not found', 404);
            }
            
            if ($data['quantity'] > $item['stock_quantity']) {
                throw new Exception('Insufficient stock', 400);
            }
            
            if ($data['quantity'] <= 0) {
                $this->executeQuery(
                    "DELETE FROM cart_items WHERE cart_item_id = ? AND customer_id = ?",
                    [$data['cart_item_id'], $this->userId]
                );
            } else {
                $this->executeQuery(
                    "UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND customer_id = ?",
                    [$data['quantity'], $data['cart_item_id'], $this->userId]
                );
            }
            
            $this->commit();
            ApiResponse::success(null, 'Cart updated successfully');
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function removeFromCart() {
        $this->authenticate('customer');
        $data = ApiResponse::getRequestData();
        ApiResponse::validateRequest(['cart_item_id'], $data);
        
        $result = $this->executeQuery(
            "DELETE FROM cart_items WHERE cart_item_id = ? AND customer_id = ?",
            [$data['cart_item_id'], $this->userId]
        );
        
        if ($result->rowCount() === 0) {
            throw new Exception('Cart item not found', 404);
        }
        
        ApiResponse::success(null, 'Item removed from cart');
    }

    public function getOrderSummary() {
        $this->authenticate('customer');
        
        $items = $this->fetchAll(
            "SELECT ci.quantity,
            t.price,
            tm.name as team_name,
            e.name as edition_name,
            t.size,
            (t.price * ci.quantity) as subtotal
            FROM cart_items ci
            JOIN tshirts t ON ci.tshirt_id = t.tshirt_id
            JOIN teams tm ON t.team_id = tm.team_id
            JOIN editions e ON t.edition_id = e.edition_id
            WHERE ci.customer_id = ?",
            [$this->userId]
        );
        
        $subtotal = array_sum(array_column($items, 'subtotal'));
        $total = $subtotal; // Add tax calculation logic here if needed
        
        ApiResponse::success([
            'items' => $items,
            'summary' => [
                'subtotal' => $subtotal,
                'total' => $total,
                'item_count' => count($items)
            ]
        ]);
    }

    public function processRequest() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        $handlers = [
            '/cart' => [
                'GET' => [$this, 'getCartItems'],
                'POST' => [$this, 'addToCart'],
                'PUT' => [$this, 'updateCartItem'],
                'DELETE' => [$this, 'removeFromCart']
            ],
            '/cart/summary' => [
                'GET' => [$this, 'getOrderSummary']
            ]
        ];

        if (!isset($handlers[$uri][$method])) {
            ApiResponse::error('Not found', 404);
        }

        $this->handleRequest($method, $handlers[$uri]);
    }
}