import "./style.scss";

// Function to fetch user profile data
async function fetchProfileData() {
  try {
    const response = await fetch('http://localhost:8000/src/api/accounts/read.php');
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
    const response = await fetch('http://localhost:8000/src/api/orders/read.php');
    if (!response.ok) throw new Error('Failed to fetch orders');
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error fetching orders:', error);
    return [];
  }
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
}

// Initialize profile page
async function initProfile() {
  const profile = await fetchProfileData();
  const orders = await fetchOrders();
  updateProfileUI(profile, orders);
}

// Load profile data when page loads
document.addEventListener('DOMContentLoaded', initProfile);

// Handle address change button
document.querySelector('.Cambioindirizzo').addEventListener('click', () => {
  // TODO: Implement address change functionality
  alert('Address change functionality will be implemented soon');
});
