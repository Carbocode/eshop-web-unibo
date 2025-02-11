import "./style.scss";
import { getToken } from "@common";
let ordersdb = null;

const API_BASE = "http://localhost:8000/src/api";

// Load data on page load
document.addEventListener('DOMContentLoaded', () => {
    loadEditions();
    loadTeams();
    loadSizes();
    loadTshirts();
    loadInventory();
    loadOrderStatuses();
    loadOrders();
});

// T-Shirt Management
async function loadTshirts() {
    try {
        const response = await fetch(`${API_BASE}/product/read_one.php`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        if (!response.ok) throw new Error('Failed to fetch t-shirts');
        const data = await response.json();
        
        const tbody = document.querySelector('#tshirtsTable tbody');
        tbody.innerHTML = data.filter(t=> t.tshirt.tshirt_id !=null).map(team =>
            `
                <tr>
                    <td>${team.team_name}</td>
                    <td>${team.tshirt.edition_name} (${team.tshirt.edition_year})</td>
                    <td>${formatCurrency(team.tshirt.price)}</td>
                    <td>
                        <img src="${team.tshirt.image_url}" alt="T-shirt image" style="width: 50px; height: 50px; object-fit: cover;">
                    </td>
                </tr>
            `
        ).join('');
    } catch (error) {
        console.error('Failed to load t-shirts:', error);
        showError('Failed to load t-shirts');
    }
}

// Edition Management
async function loadEditions() {
    try {
        const response = await fetch(`${API_BASE}/editions/read.php`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        if (!response.ok) throw new Error('Failed to fetch editions');
        const editions = await response.json();
        
        // Update editions table
        const tbody = document.querySelector('#editionsTable tbody');
        tbody.innerHTML = editions.map(edition => `
            <tr>
                <td>${edition.name}</td>
                <td>${edition.year}</td>
                <td>${edition.description || ''}</td>
            </tr>
        `).join('');

        // Update edition dropdown in t-shirt form
        const select = document.querySelector('#tshirtEdition');
        select.innerHTML = editions.map(edition => `
            <option value="${edition.edition_id}">${edition.name} (${edition.year})</option>
        `).join('');
    } catch (error) {
        console.error('Failed to load editions:', error);
        showError('Failed to load editions');
    }
}

document.querySelector('#editionForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = {
        name: formData.get('name'),
        year: parseInt(formData.get('year')),
        description: formData.get('description')
    };

    try {
        const response = await fetch(`${API_BASE}/editions/create.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getToken()}`
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to create edition');
        }

        e.target.reset();
        loadEditions();
    } catch (error) {
        console.error('Failed to create edition:', error);
        showError(error.message);
    }
});

// Team Management
async function loadTeams() {
    try {
        const response = await fetch(`${API_BASE}/teams/read.php`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        if (!response.ok) throw new Error('Failed to fetch teams');
        const teams = await response.json();
        
        const select = document.querySelector('#tshirtTeam');
        select.innerHTML = teams.map(team => `
            <option value="${team.team_id}">${team.name}</option>
        `).join('');
    } catch (error) {
        console.error('Failed to load teams:', error);
        showError('Failed to load teams');
    }
}

// T-Shirt Management
document.querySelector('#tshirtForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = {
        team_id: parseInt(formData.get('team_id')),
        edition_id: parseInt(formData.get('edition_id')),
        price: parseFloat(formData.get('price')),
        image_url: formData.get('image_url')
    };

    try {
        const response = await fetch(`${API_BASE}/tshirts/create.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getToken()}`
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to create t-shirt');
        }

        e.target.reset();
        loadInventory();
        loadTshirts();
    } catch (error) {
        console.error('Failed to create t-shirt:', error);
        showError(error.message);
    }
});

// Size Management
async function loadSizes() {
    try {
        const response = await fetch(`${API_BASE}/sizes/read.php`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        if (!response.ok) throw new Error('Failed to fetch sizes');
        const sizes = await response.json();
        
        const select = document.querySelector('#inventorySize');
        select.innerHTML = sizes.map(size => `
            <option value="${size.size_id}">${size.name}</option>
        `).join('');
    } catch (error) {
        console.error('Failed to load sizes:', error);
        showError('Failed to load sizes');
    }
}

// Inventory Management
async function loadInventory() {
    try {
        // Load t-shirts for inventory dropdown
        const tshirtsResponse = await fetch(`${API_BASE}/product/read_one.php`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        if (!tshirtsResponse.ok) throw new Error('Failed to fetch t-shirts');
        const tshirts = await tshirtsResponse.json();
        
        const select = document.querySelector('#inventoryTshirt');     
        select.innerHTML = tshirts.filter(t=> t.tshirt.tshirt_id !=null).map(tshirt => `
            <option value="${tshirt.tshirt.tshirt_id}">
                ${tshirt.team_name} - ${tshirt.tshirt.edition_name}
            </option>
        `).join('');

        // Load current inventory
        const inventoryResponse = await fetch(`${API_BASE}/warehouse/read.php`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        if (!inventoryResponse.ok) throw new Error('Failed to fetch inventory');
        const inventory = await inventoryResponse.json();
        
        const tbody = document.querySelector('#inventoryTable tbody');
        tbody.innerHTML = inventory.map(item => `
            <tr>
                <td>${item.team_name} - ${item.edition_name}</td>
                <td>${item.size_name}</td>
                <td>${item.availability}</td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Failed to load inventory:', error);
        showError('Failed to load inventory');
    }
}

document.querySelector('#inventoryForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = {
        tshirt_id: parseInt(formData.get('tshirt_id')),
        size_id: parseInt(formData.get('size_id')),
        availability: parseInt(formData.get('availability'))
    };

    try {
        const response = await fetch(`${API_BASE}/warehouse/create.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getToken()}`
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to update inventory');
        }

        e.target.reset();
        loadInventory();
    } catch (error) {
        console.error('Failed to update inventory:', error);
        showError(error.message);
    }
});

// Helper function to show errors
function showError(message) {
    alert(message);
}

// Helper function to format currency
function formatCurrency(value) {
    return new Intl.NumberFormat('it-IT', {
        style: 'currency',
        currency: 'EUR'
    }).format(value || 0);
}
// Order Status Management
async function loadOrderStatuses() {
    try {
        const response = await fetch(`${API_BASE}/orders/statuses/read.php`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        if (!response.ok) throw new Error('Failed to fetch order statuses');
        const statuses = await response.json();
        
        const select = document.querySelector('#orderStatus');
        select.innerHTML = statuses.map(status => `
            <option value="${status.status_id}">${status.status}</option>
        `).join('');
    } catch (error) {
        console.error('Failed to load order statuses:', error);
        showError('Failed to load order statuses');
    }
}

// Order Management
async function loadOrders() {
    try {
        const response = await fetch(`${API_BASE}/orders/read_all.php`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        if (!response.ok) throw new Error('Failed to fetch orders');
        const orders = await response.json();
        ordersdb = orders;
        const select = document.querySelector('#orderSelect');
        select.innerHTML = orders.map(order => `
            <option value="${order.order_id}" onclick="loadOrderDetails(${order.order_id})">
                Order #${order.order_id} - ${order.status}
            </option>
        `).join('');
    } catch (error) {
        console.error('Failed to load orders:', error);
        showError('Failed to load orders');
    }
}


document.querySelector('#orderSelect')?.addEventListener('change', (e) => {
    const order_id = parseInt(e.target.value, 10);
    loadOrderDetails(order_id);
});
// Order Update Handler
document.querySelector('#orderUpdateForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = {
        order_id: parseInt(formData.get('order_id'), 10),
        status_id: parseInt(formData.get('status_id'), 10),
        tracking_number: formData.get('tracking_number') || null,
        delivery_date: formData.get('delivery_date') || null,
        shipping_agent: formData.get('shipping_agent') || null
    };

    try {
        const response = await fetch(`${API_BASE}/orders/update_status.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getToken()}`
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to update order');
        }

        e.target.reset();
        loadOrders(); // Refresh orders list
        alert('Ordine Aggiornato')
    } catch (error) {
        console.error('Failed to update order:', error);
        alert(error.message)
    }
});
async function loadOrderDetails(order_id){
    const order = ordersdb.find(o => o.order_id == order_id);
    document.querySelector('#orderStatus').value = order.status_id;
    document.querySelector('#trackingNumber').value = order.tracking_number;
    document.querySelector('#deliveryDate').value = order.delivery;
    document.querySelector('#shippingAgent').value = order.shipping_agent;
}