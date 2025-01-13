import { getToken } from "@common";
import "./style.scss";
let token = getToken();
let orders=null;
let profile=null;

// Function to fetch user profile data
async function fetchProfileData() {
  try {
    const response = await fetch('http://localhost:8000/src/api/accounts/read.php',{
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
      },
    });
    if (!response.ok) throw new Error('Failed to fetch profile data');
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error fetching profile:', error);
    return null;
  }
}

// Function to fetch user orders
async function fetchOrders() {
  try {
    const response = await fetch('http://localhost:8000/src/api/orders/read_all.php', {
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });
    if (!response.ok) throw new Error('Failed to fetch orders');
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error fetching orders:', error);
    return [];
  }
}

// Function to format date
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('it-IT');
}

// Function to update profile UI
function updateProfileUI(profile, orders) {
  if (!profile) return;

  // Update profile info
  document.querySelector('.infoProfilo h4').textContent = profile.full_name;
  
  // Update address
  const addressText = `${profile.address}, ${profile.city} (${profile.province})`;
  document.querySelector('.dettagliProfilo p:nth-child(2)').textContent = addressText;
  
  // Update statistics
  const totalOrders = orders.length;
  const deliveredOrders = orders.filter(order => order.status === 'delivered').length;
  
  document.querySelector('.statistiche:nth-child(1) p:last-child').textContent = totalOrders;
  document.querySelector('.statistiche:nth-child(2) p:last-child').textContent = deliveredOrders;

  // Update orders table
  const ordersTableBody = document.getElementById('ordini-list');
  ordersTableBody.innerHTML = ''; // Clear existing rows

  orders.forEach(order => {
    const row = document.createElement('tr');
    row.style.cursor = 'pointer';
    row.onclick = () => window.location.href = `/src/pages/order-tracking/?id=${order.order_id}`;
    
    row.innerHTML = `
      <td>#${order.order_id}</td>
      <td>${formatDate(order.delivery)}</td>
      <td>
        <span class="status-badge ${order.status.toLowerCase()}">
          <i class="fa-solid ${order.icon}"></i>
          ${order.status}
        </span>
      </td>
      <td>${new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(order.total)}</td>
      <td>${order.items.length} articoli</td>
    `;
    
    ordersTableBody.appendChild(row);
  });
}

// Initialize profile page
async function initProfile() {
  profile = await fetchProfileData();
  orders = await fetchOrders();
  updateProfileUI(profile, orders);
}

// Load profile data when page loads
document.addEventListener('DOMContentLoaded', initProfile);

// Handle address change button
document.querySelector('.Cambioindirizzo').addEventListener('click', () => {
  // TODO: Implement address change functionality
  alert('Address change functionality will be implemented soon');
});
