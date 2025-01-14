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
      <td>${order.delivery ? formatDate(order.delivery): "In Transito"}</td>
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

// Function to update profile
async function updateProfile(profileData) {
  try {
    const response = await fetch('http://localhost:8000/src/api/accounts/update.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(profileData)
    });

    if (!response.ok) throw new Error('Failed to update profile');
    
    // Refresh profile data
    profile = await fetchProfileData();
    updateProfileUI(profile, orders);
    return true;
  } catch (error) {
    console.error('Error updating profile:', error);
    return false;
  }
}

// Handle profile change button
document.querySelector('.Cambioindirizzo').textContent = 'Modifica Profilo';
document.querySelector('.Cambioindirizzo').addEventListener('click', () => {
  // Create modal HTML
  const modal = document.createElement('div');
  modal.className = 'address-modal';
  modal.innerHTML = `
    <div class="modal-content">
      <h3>Modifica Profilo</h3>
      <form id="profile-form">
        <div class="form-group">
          <label for="full_name">Nome Completo:</label>
          <input type="text" id="full_name" required value="${profile.full_name}">
        </div>
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" required value="${profile.email}">
        </div>
        <div class="form-group">
          <label for="phone">Telefono:</label>
          <input type="tel" id="phone" required value="${profile.phone}">
        </div>
        <div class="form-group">
          <label for="address">Indirizzo:</label>
          <input type="text" id="address" required value="${profile.address}">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="city">Città:</label>
            <input type="text" id="city" required value="${profile.city}">
          </div>
          <div class="form-group">
            <label for="province">Provincia:</label>
            <input type="text" id="province" required value="${profile.province}">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="zip">CAP:</label>
            <input type="text" id="zip" required value="${profile.zip}">
          </div>
          <div class="form-group">
            <label for="country">Paese:</label>
            <input type="text" id="country" required value="${profile.country}">
          </div>
        </div>
        <div class="button-group">
          <button type="submit" class="save-btn">Salva</button>
          <button type="button" class="cancel-btn">Annulla</button>
        </div>
      </form>
    </div>
  `;

  // Add modal to page
  document.body.appendChild(modal);

  // Add event listeners
  const form = modal.querySelector('#profile-form');
  const cancelBtn = modal.querySelector('.cancel-btn');

  cancelBtn.addEventListener('click', () => {
    document.body.removeChild(modal);
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const profileData = {
      full_name: form.querySelector('#full_name').value,
      email: form.querySelector('#email').value,
      phone: form.querySelector('#phone').value,
      address: form.querySelector('#address').value,
      city: form.querySelector('#city').value,
      province: form.querySelector('#province').value,
      zip: form.querySelector('#zip').value,
      country: form.querySelector('#country').value
    };

    const success = await updateProfile(profileData);
    if (success) {
      document.body.removeChild(modal);
      window.location.reload();
    } else {
      alert('Si è verificato un errore durante l\'aggiornamento del profilo. Riprova più tardi.');
    }
  });
});
