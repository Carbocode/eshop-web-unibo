<?php

namespace App\Controllers;

use Exception;
use PDOException;
use App\Utils\ApiResponse;

/**
 * OrderController handles order-related operations including
 * retrieving order details and updating order statuses.
 */
class OrderController extends BaseController {
    /**
     * Processes incoming HTTP requests and routes them to appropriate handlers.
     * Handles GET requests for order details and PUT requests for status updates.
     * 
     * @return void
     */
    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $orderId = $this->getOrderIdFromUri($uri);
        $handlers = array(
            'GET' => [$this,'getOrder'],
            'PUT' => [$this,'updateOrderSatus']
        );
        $this->handleRequest($method,$handlers);
        return;
    }

    /**
     * Extracts order ID from the URI path.
     * 
     * @param string $uri The request URI
     * @return string|null The order ID if found, null otherwise
     */
    private function getOrderIdFromUri($uri) {
        if (preg_match('/\/orders\/(\d+)/', $uri, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Retrieves detailed information about a specific order.
     * Includes order items, shipping details, and total calculations.
     * 
     * @param string $orderId The ID of the order to retrieve
     * @throws Exception When order not found or database error occurs
     * @return void
     */
    private function getOrder($orderId = null) {
        try {
            if($orderId==null){
                $orderId = $this->getOrderIdFromUri(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
            }
            // Validate order ID
            if (!$orderId) {
                ApiResponse::error("Order ID is required", 400);
            }

            // Get order details from database
            $query = "SELECT o.*, os.status, os.updated_at as status_updated_at
                     FROM orders o
                     JOIN order_status os ON o.id = os.order_id
                     WHERE o.id = ?";
            
            $order = $this->fetch($query, [$orderId]);
            
            if (!$order) {
                ApiResponse::error("Order not found", 404);
            }

            // Get order items
            $itemsQuery = "SELECT oi.*, p.name, p.image
                          FROM order_items oi
                          JOIN products p ON oi.product_id = p.id
                          WHERE oi.order_id = ?";
            
            $items = $this->fetchAll($itemsQuery, [$orderId]);

            // Get shipping details
            $shippingQuery = "SELECT *
                            FROM order_shipping
                            WHERE order_id = ?";
            
            $shipping = $this->fetch($shippingQuery, [$orderId]);

            // Format response
            $response = [
                "id" => $order->id,
                "status" => $order->status,
                "created_at" => $order->created_at,
                "updated_at" => $order->updated_at,
                "items" => array_map(function($item) {
                    return [
                        "id" => $item->id,
                        "name" => $item->name,
                        "image" => $item->image,
                        "quantity" => $item->quantity,
                        "price" => (float)$item->price
                    ];
                }, $items),
                "shipping" => [
                    "address" => [
                        "name" => $shipping->recipient_name,
                        "street" => $shipping->street_address,
                        "cityState" => $shipping->city . ", " . $shipping->state . " " . $shipping->postal_code,
                        "country" => $shipping->country
                    ],
                    "tracking" => [
                        "number" => $shipping->tracking_number,
                        "eta" => $shipping->estimated_delivery_date,
                        "method" => $shipping->shipping_method
                    ]
                ],
                "totals" => [
                    "subtotal" => (float)$order->subtotal,
                    "shipping" => (float)$order->shipping_cost,
                    "tax" => (float)$order->tax,
                    "final" => (float)$order->total
                ]
            ];

            ApiResponse::success($response);
        } catch (Exception $e) {
            ApiResponse::error("Failed to retrieve order details: " . $e->getMessage(), 500);
        }
    }

    /**
     * Updates the status of a specific order.
     * 
     * @param string $orderId The ID of the order to update
     * @param string $status The new status to set. Must be one of: placed, processing, shipped, delivered
     * @throws Exception When order not found, invalid status, or database error occurs
     * @return void
     */
    private function updateOrderStatus($orderId, $status) {
        try {
            // Validate status
            $validStatuses = ['placed', 'processing', 'shipped', 'delivered'];
            if (!in_array($status, $validStatuses)) {
                ApiResponse::error("Invalid status", 400);
            }

            // Update status
            $query = "UPDATE order_status 
                     SET status = ?, updated_at = NOW() 
                     WHERE order_id = ?";
            
            $result = $this->executeQuery($query, [$status, $orderId]);
            
            if ($result->rowCount() === 0) {
                ApiResponse::error("Order not found", 404);
            }

            ApiResponse::success(["message" => "Order status updated successfully"]);
        } catch (Exception $e) {
            ApiResponse::error("Failed to update order status: " . $e->getMessage(), 500);
        }
    }
}