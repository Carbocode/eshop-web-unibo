<?php

/**
 * Helper functions for creating notifications
 */

/**
 * Create a new notification
 * @param int $user_id - ID of the user to notify
 * @param string $type - Type of notification (order_status, stock_alert, etc.)
 * @param string $message - Notification message
 * @return bool - Success status
 */
function create_notification($user_id, $type, $message) {
    global $conn;
    
    $sql = "
        INSERT INTO notifications (user_id, type, message, created_at, is_read)
        VALUES (?, ?, ?, NOW(), 0)
    ";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Failed to prepare notification creation query");
        return false;
    }
    
    $stmt->bind_param('iss', $user_id, $type, $message);
    $success = $stmt->execute();
    
    $stmt->close();
    return $success;
}

/**
 * Create order status change notification
 * @param int $order_id - ID of the order
 * @param string $new_status - New status description
 * @param int $customer_id - ID of the customer to notify
 * @return bool - Success status
 */
function create_order_status_notification($order_id, $new_status, $customer_id) {
    $message = "Il tuo ordine #$order_id è stato aggiornato a: $new_status";
    return create_notification($customer_id, 'order_status', $message);
}

/**
 * Create low stock notification for admins
 * @param string $product_name - Name of the product
 * @param int $current_stock - Current stock level
 * @return bool - Success status
 */
function create_stock_alert_notification($product_name, $current_stock) {
    $message = "Attenzione: Il prodotto '$product_name' ha scorte limitate ($current_stock rimasti)";
    return create_notification(-1, 'stock_alert', $message);
}

/**
 * Create new order notification for admins
 * @param int $order_id - ID of the new order
 * @param float $total - Order total amount
 * @return bool - Success status
 */
function create_new_order_notification($order_id, $total) {
    $message = "Nuovo ordine #$order_id ricevuto! Totale: €" . number_format($total, 2);
    return create_notification(-1, 'new_order', $message);
}

/**
 * Create order placed notification for customer
 * @param int $order_id - ID of the new order
 * @param float $total - Order total amount
 * @param int $customer_id - ID of the customer who placed the order
 * @return bool - Success status
 */
function create_order_placed_notification($order_id, $total, $customer_id) {
    $message = "Il tuo ordine #$order_id è stato confermato! Totale: €" . number_format($total, 2);
    return create_notification($customer_id, 'order_placed', $message);
}

/**
 * Check if stock level requires notification
 * @param int $current_stock - Current stock level
 * @param int $threshold - Threshold for low stock alert (default 5)
 * @return bool - True if notification should be sent
 */
function should_notify_low_stock($current_stock, $threshold = 5) {
    return $current_stock <= $threshold;
}