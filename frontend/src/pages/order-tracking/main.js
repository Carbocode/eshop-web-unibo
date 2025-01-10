import "../../assets/styles/normalize.css";
import "./style.scss";

const API_BASE_URL = "http://localhost:8000";

class OrderTracker {
  constructor() {
    this.orderId = new URLSearchParams(window.location.search).get("orderId");
    this.elements = {
      timeline: document.querySelector(".order-tracking"),
      orderItems: document.querySelector('[data-order="items"]'),
      addressFields: {
        name: document.querySelector('[data-address="name"]'),
        street: document.querySelector('[data-address="street"]'),
        cityState: document.querySelector('[data-address="city-state"]'),
        country: document.querySelector('[data-address="country"]'),
      },
      trackingFields: {
        number: document.querySelector('[data-tracking="number"]'),
        eta: document.querySelector('[data-tracking="eta"]'),
        method: document.querySelector('[data-tracking="method"]'),
      },
      totals: {
        subtotal: document.querySelector('[data-total="subtotal"]'),
        shipping: document.querySelector('[data-total="shipping"]'),
        tax: document.querySelector('[data-total="tax"]'),
        final: document.querySelector('[data-total="final"]'),
      },
      loading: document.querySelector(".loading-overlay"),
      error: document.querySelector(".error-message"),
    };

    this.init();
  }

  async init() {
    if (!this.orderId) {
      this.showError("Order ID is missing. Please check your URL.");
      return;
    }

    try {
      await this.fetchOrderData();
      this.setupPolling();
    } catch (error) {
      this.showError("Failed to load order details. Please try again later.");
      console.error("Error initializing order tracking:", error);
    }
  }

  async fetchOrderData() {
    this.showLoading(true);
    try {
      const response = await fetch(`${API_BASE_URL}/orders/${this.orderId}`, {
        headers: {
          Accept: "application/json",
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || "Failed to fetch order data");
      }

      const data = await response.json();
      if (data.status === "success") {
        this.updateUI(data.data);
      } else {
        throw new Error(data.message || "Failed to fetch order data");
      }
    } catch (error) {
      throw error;
    } finally {
      this.showLoading(false);
    }
  }

  updateUI(data) {
    // Update timeline status
    this.updateTimeline(data.status);

    // Update shipping details
    this.updateShippingDetails(data.shipping);

    // Update order items
    this.updateOrderItems(data.items);

    // Update order totals
    this.updateOrderTotals(data.totals);
  }

  updateTimeline(status) {
    const steps = ["placed", "processing", "shipped", "delivered"];
    const currentStepIndex = steps.indexOf(status);

    steps.forEach((step, index) => {
      const stepElement = this.elements.timeline.children[index];
      if (index < currentStepIndex) {
        stepElement.classList.add("completed");
      } else if (index === currentStepIndex) {
        stepElement.classList.add("active");
      }
    });
  }

  updateShippingDetails(shipping) {
    // Update address fields
    Object.entries(this.elements.addressFields).forEach(([key, element]) => {
      element.textContent = shipping.address[key] || "";
    });

    // Update tracking fields
    Object.entries(this.elements.trackingFields).forEach(([key, element]) => {
      element.textContent = shipping.tracking[key] || "";
    });
  }

  updateOrderItems(items) {
    this.elements.orderItems.innerHTML = items
      .map(
        (item) => `
      <div class="item">
        <img src="${item.image}" alt="${item.name}" />
        <div class="item-details">
          <h4>${item.name}</h4>
          <p>Quantity: ${item.quantity}</p>
          <p>Price: €${item.price.toFixed(2)}</p>
        </div>
        <div class="item-total">
          €${(item.price * item.quantity).toFixed(2)}
        </div>
      </div>
    `
      )
      .join("");
  }

  updateOrderTotals(totals) {
    Object.entries(this.elements.totals).forEach(([key, element]) => {
      element.textContent = totals[key]
        ? `€${totals[key].toFixed(2)}`
        : "€0.00";
    });
  }

  setupPolling() {
    // Poll for updates every 30 seconds
    this.pollingInterval = setInterval(() => {
      this.fetchOrderData().catch((error) => {
        console.error("Error polling order data:", error);
      });
    }, 30000);
  }

  showLoading(show) {
    this.elements.loading.classList.toggle("active", show);
  }

  showError(message) {
    this.elements.error.textContent = message;
    this.elements.error.classList.add("active");
    setTimeout(() => {
      this.elements.error.classList.remove("active");
    }, 5000);
  }

  cleanup() {
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
    }
  }
}

// Initialize the order tracker when the DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  const orderTracker = new OrderTracker();

  // Cleanup on page unload
  window.addEventListener("unload", () => {
    orderTracker.cleanup();
  });
});
