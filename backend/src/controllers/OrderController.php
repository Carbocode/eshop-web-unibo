<?php

namespace App\Controllers;

use Exception;
use PDOException;
use App\Utils\ApiResponse;

class OrderController extends BaseController {
    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $orderId = $this->getOrderIdFromUri($uri);

        switch ($method) {
            case 'GET':
                if ($orderId) {
                    $this->getOrder($orderId);
                } else {
                    ApiResponse::error('Order ID is required', 400);
                }
                break;

            case 'PUT':
                if ($orderId) {
                    $data = ApiResponse::getRequestData();
                    if (!isset($data['status'])) {
                        ApiResponse::error('Status is required', 400);
                    }
                    $this->updateOrderStatus($orderId, $data['status']);
                } else {
                    ApiResponse::error('Order ID is required', 400);
                }
                break;

            default:
                ApiResponse::error('Method not allowed', 405);
        }
    }

    private function getOrderIdFromUri($uri) {
        if (preg_match('/\/orders\/(\d+)/', $uri, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function getOrder($orderId) {
        try {
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