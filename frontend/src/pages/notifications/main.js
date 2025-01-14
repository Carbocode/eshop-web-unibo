import "./style.scss";
import { getToken } from "@common";
/**
 * NotificationsManager Class
 * Handles all notification-related functionality including:
 * - Fetching and displaying notifications
 * - Managing notification status (read/unread)
 * - Filtering notifications
 * - Real-time updates through polling
 * - Deletion of notifications
 */
class NotificationsManager {
  /**
   * Initialize the notifications manager
   * Sets up initial state, event listeners, and starts notification polling
   */
  constructor() {
    // Store notifications data
    this.notifications = [];
    // Current filter state (all/read/unread)
    this.filter = 'all';
    // Cache DOM elements
    this.template = document.getElementById('notification-template');
    this.container = document.querySelector('.notifications-list');
    
    // Initialize the component
    this.setupEventListeners();
    this.fetchNotifications();
    this.setupPolling();
  }

  /**
   * Set up event listeners for user interactions
   * - Filter buttons for showing all/read/unread notifications
   * - Mark as read/unread button
   * - Delete notification button
   */
  setupEventListeners() {
    // Handle filter button clicks
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        // Update active filter button
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        // Update filter and re-render
        this.filter = btn.dataset.filter;
        this.renderNotifications();
      });
    });

    // Use event delegation for notification actions
    this.container.addEventListener('click', (e) => {
      const notificationItem = e.target.closest('.notification-item');
      if (!notificationItem) return;

      const notificationId = notificationItem.dataset.id;

      // Handle mark as read/unread
      if (e.target.closest('.mark-btn')) {
        this.toggleNotificationStatus(notificationId);
      } 
      // Handle delete notification
      else if (e.target.closest('.delete-btn')) {
        this.deleteNotification(notificationId);
      }
    });
  }

  /**
   * Fetch notifications from the backend
   * Updates the local notifications array and renders the UI
   */
  async fetchNotifications() {
    try {
      const response = await fetch('http://localhost:8000/src/api/notifications/read.php', {
        headers: {
          'Authorization': `Bearer ${getToken()}`
        }
      });
      
      if (!response.ok) throw new Error('Failed to fetch notifications');
      
      this.notifications = await response.json();
      this.renderNotifications();
    } catch (error) {
      console.error('Error fetching notifications:', error);
    }
  }

  /**
   * Set up polling mechanism for new notifications
   * Checks for new notifications every 30 seconds
   */
  setupPolling() {
    // 30 second interval for checking new notifications
    setInterval(() => this.checkNewNotifications(), 30000);
  }

  /**
   * Check for new notifications
   * If new notifications exist, triggers a full refresh
   */
  async checkNewNotifications() {
    try {
      const response = await fetch('http://localhost:8000/src/api/notifications/check_new.php', {
        headers: {
          'Authorization': `Bearer ${getToken()}`
        }
      });
      
      if (!response.ok) throw new Error('Failed to check new notifications');
      
      const result = await response.json();
      // Refresh notifications list if new ones exist
      if (result.hasNew) {
        this.fetchNotifications();
      }
    } catch (error) {
      console.error('Error checking new notifications:', error);
    }
  }

  /**
   * Toggle the read/unread status of a notification
   * Updates both backend and local state
   * @param {string} notificationId - ID of the notification to toggle
   */
  async toggleNotificationStatus(notificationId) {
    try {
      const response = await fetch('http://localhost:8000/src/api/notifications/update.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${getToken()}`
        },
        body: JSON.stringify({
          notification_id: notificationId,
          action: 'toggle_status'
        })
      });

      if (!response.ok) throw new Error('Failed to update notification status');

      // Update local state and re-render
      const notification = this.notifications.find(n => n.id === parseInt(notificationId));
      if (notification) {
        notification.read = !notification.read;
        //location.reload();
        this.renderNotifications();
      }
    } catch (error) {
      console.error('Error updating notification status:', error);
    }
  }

  /**
   * Delete a notification
   * Removes from both backend and local state
   * @param {string} notificationId - ID of the notification to delete
   */
  async deleteNotification(notificationId) {
    try {
      const response = await fetch('http://localhost:8000/src/api/notifications/delete.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${getToken()}`
        },
        body: JSON.stringify({
          notification_id: notificationId
        })
      });

      if (!response.ok) throw new Error('Failed to delete notification');

      // Update local state and re-render
      this.notifications = this.notifications.filter(n => n.id !== parseInt(notificationId));
      this.renderNotifications();
    } catch (error) {
      console.error('Error deleting notification:', error);
    }
  }

  /**
   * Get the appropriate icon class based on notification type
   * @param {string} type - Type of notification
   * @returns {string} FontAwesome icon class
   */
  getNotificationIcon(type) {
    const icons = {
      order_status: 'fa-box',           // Order status updates
      stock_alert: 'fa-exclamation-triangle', // Low stock alerts
      payment: 'fa-credit-card',        // Payment related
      delivery: 'fa-truck',             // Delivery updates
      default: 'fa-bell'                // Default icon
    };
    return icons[type] || icons.default;
  }

  /**
   * Format timestamp into relative time
   * @param {string} timestamp - ISO timestamp
   * @returns {string} Formatted time string in Italian
   */
  formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    // Show time if less than 24 hours old
    if (diff < 86400000) {
      return date.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
    }
    // Show day name if less than a week old
    if (diff < 604800000) {
      return date.toLocaleDateString('it-IT', { weekday: 'long' });
    }
    // Show full date for older notifications
    return date.toLocaleDateString('it-IT');
  }

  /**
   * Render notifications in the UI
   * Handles filtering, empty states, and notification display
   */
  renderNotifications() {
    this.container.innerHTML = '';
    
    // Apply current filter
    let filteredNotifications = this.notifications;
    if (this.filter === 'read') {
      filteredNotifications = this.notifications.filter(n => n.read);
    } else if (this.filter === 'unread') {
      filteredNotifications = this.notifications.filter(n => !n.read);
    }

    // Show empty state if no notifications
    if (filteredNotifications.length === 0) {
      const emptyMessage = document.createElement('p');
      emptyMessage.textContent = 'Nessuna notifica';
      emptyMessage.className = 'empty-notifications';
      this.container.appendChild(emptyMessage);
      return;
    }

    // Render each notification
    filteredNotifications.forEach(notification => {
      const clone = this.template.content.cloneNode(true);
      const item = clone.querySelector('.notification-item');
      
      // Set notification ID and read status
      item.dataset.id = notification.id;
      if (!notification.read) item.classList.add('unread');

      // Set appropriate icon
      const icon = clone.querySelector('.notification-icon i');
      icon.classList.add(this.getNotificationIcon(notification.type));

      // Set message and time
      const message = clone.querySelector('.notification-message');
      message.textContent = notification.message;

      const time = clone.querySelector('.notification-time');
      time.textContent = this.formatTime(notification.timestamp);

      // Set read/unread icon
      const markBtn = clone.querySelector('.mark-btn i');
      markBtn.classList.add(notification.read ? 'fa-circle-check' : 'fa-circle');

      this.container.appendChild(clone);
    });
  }
}

// Initialize the notifications manager when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
  new NotificationsManager();
});
