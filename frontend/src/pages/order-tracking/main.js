import "./style.scss";

document.addEventListener("DOMContentLoaded", () => {
  const orderId = getOrderIdFromURL();
  const statusesEndpoint =
    "http://localhost:8000/src/api/orders/statuses/read.php"; // API per gli stati
  const orderEndpoint = `http://localhost:8000/src/api/orders/read.php?order_id=${orderId}`;

  if (!orderId) {
    showError("Order ID not found in the URL.");
    return;
  }

  // Carica gli stati disponibili
  fetch(statusesEndpoint)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Failed to fetch order statuses");
      }
      return response.json();
    })
    .then((statuses) => {
      generateSteps(statuses);

      // Carica i dettagli dell'ordine dopo aver creato gli step
      return fetch(orderEndpoint, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        },
      });
    })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Failed to fetch order details");
      }
      return response.json();
    })
    .then((data) => {
      if (data.error) {
        showError(data.error);
      } else {
        updateOrderTracking(data);
        populateItemsTable(data.items);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showError("An error occurred while loading the page.");
    });
});

// Funzione per ottenere l'order_id dall'URL
function getOrderIdFromURL() {
  const params = new URLSearchParams(window.location.search);
  return params.get("id");
}

// Funzione per generare gli steps dinamicamente
function generateSteps(statuses) {
  const stepsContainer = document.querySelector(".steps-container");
  stepsContainer.innerHTML = ""; // Pulisce il contenitore degli step

  statuses.forEach((status) => {
    const stepElement = document.createElement("li");
    stepElement.innerHTML = `
      <i class="fa-solid ${status.icon}"></i>
      <p>${status.status}</p>
    `;
    stepsContainer.appendChild(stepElement);
  });
}

// Funzione per aggiornare lo stato attivo degli step e visualizzare i dettagli
function updateOrderTracking(order) {
  const steps = document.querySelectorAll(".steps-container li");
  let isActive = true;

  steps.forEach((step) => {
    const stepLabel = step.querySelector("p").textContent.toLowerCase();
    if (isActive) {
      step.classList.add("active");
    }
    if (stepLabel === order.status.toLowerCase()) {
      isActive = false;
    }
  });

  // Aggiorna indirizzo di spedizione e dettagli dell'ordine
  document.querySelector('[data-address="name"]').textContent =
    order.full_name || "N/A";
  document.querySelector('[data-address="street"]').textContent =
    order.address || "N/A";
  document.querySelector('[data-address="city-state"]').textContent = `${
    order.city || ""
  }, ${order.province || ""}`;
  document.querySelector('[data-address="country"]').textContent =
    order.country || "N/A";

  document.querySelector('[data-tracking="number"]').textContent =
    order.tracking_number || "N/A";
  document.querySelector('[data-tracking="eta"]').textContent =
    order.delivery || "N/A";
  document.querySelector('[data-tracking="method"]').textContent =
    order.shipping_agent || "N/A";

  document.querySelector('[data-total="subtotal"]').textContent =
    formatCurrency(order.subtotal);
  document.querySelector('[data-total="shipping"]').textContent =
    formatCurrency(order.shipping_cost);
  document.querySelector('[data-total="tax"]').textContent = formatCurrency(
    order.tax
  );
  document.querySelector('[data-total="final"]').textContent = formatCurrency(
    order.total
  );
}

// Funzione per popolare la tabella degli item
function populateItemsTable(items) {
  const itemsContainer = document.querySelector('[data-order="items"]');
  itemsContainer.innerHTML = ""; // Pulisce il contenitore degli articoli esistenti

  items.forEach((item) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td><img src="${
        item.tshirt.image_url
      }" alt="Item Image" class="item-image" /></td>
      <td>
        ${item.tshirt.edition_name} - ${item.tshirt.size_name}
      </td>
      <td>
        <a href="/src/pages/product/?id=${item.team.team_id}">
          ${item.team.team_name}
        </a>
      </td>
      <td>${item.quantity}</td>
      <td>
        ${formatCurrency(item.paid_price * item.quantity)} 
        <br>
        <small>
          ${formatCurrency(item.tshirt.price)} * ${item.quantity}
        </small>
      </td>
    `;

    itemsContainer.appendChild(row);
  });
}

// Funzione per formattare una valuta
function formatCurrency(value) {
  return new Intl.NumberFormat("it-IT", {
    style: "currency",
    currency: "EUR",
  }).format(value || 0);
}

// Funzione per mostrare errori
function showError(message) {
  const mainContent = document.querySelector(".order-tracking");
  mainContent.innerHTML = `<p class="error">${message}</p>`;
}
