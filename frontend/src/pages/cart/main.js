import "./style.scss";
import { getToken } from "@common";

const token = getToken();

async function loadCartItems() {
    try {
        const response = await fetch('http://localhost:8000/src/api/checkout/process.php?', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        const data = await response.json();
        
        if (response.ok) {
            if (data.message === "Nessun articolo nel carrello") {
                showEmptyCartMessage();
            } else {
                displayCartItems(data);
                updateCartSummary(data);
                const loaders = document.querySelectorAll('.loader');
                loaders.forEach(loader => {
                    loader.style.display = 'none';
                });
            }
        } else {
            throw new Error(data.error || 'Error loading cart items');
        }
    } catch (error) {
        console.error('Error loading cart items:', error);
        showEmptyCartMessage();
    }
}

function showEmptyCartMessage() {
    document.querySelector('.cart-container').style.display = 'none';
    document.getElementById('emptyCartMessage').style.display = 'block';
    const loaders = document.querySelectorAll('.loader');
    loaders.forEach(loader => {
        loader.style.display = 'none';
    });
}

function displayCartItems(items) {
    const cartContainer = document.getElementById('cartItems');
    let html = '';

    items.forEach(item => {
        /*
        html += `
            <div class="cart-item" data-item-id="${item.cart_item_id}">
                <div class="item-image">
                    <img src="${item.image_url}" alt="${item.team_name}">
                </div>
                <div class="item-details">
                    <h3>Maglia ${item.team_name}</h3>
                    <div class="quantity-controls">
                        <button class="quantity-btn minus" onclick="updateQuantity(${item.cart_item_id}, ${item.quantity - 1})">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button class="quantity-btn plus" onclick="updateQuantity(${item.cart_item_id}, ${item.quantity + 1})">+</button>
                    </div>
                    <button class="remove-btn" onclick="removeItem(${item.cart_item_id})">
                        <i class="fa-solid fa-trash"></i> Rimuovi
                    </button>
                </div>
                <div class="item-price">
                    €${(item.price * item.quantity).toFixed(2)}
                </div>
            </div>
        `;
        */
        html += `
        <div class="cart-item" data-item-id="${item.item_id}">
            <div class="item-image">
                <img src="${item.image_url}" alt="${item.team}">
            </div>
            <div class="item-details">
                <h3>Maglia ${item.team}</h3>
                <div class="quantity-controls">
                    <button class="quantity-btn minus" onclick="updateQuantity(${item.item_id}, ${item.quantity - 1})">-</button>
                    <span class="quantity">${item.quantity}</span>
                    <button class="quantity-btn plus" onclick="updateQuantity(${item.item_id}, ${item.quantity + 1})">+</button>
                </div>
                <button class="remove-btn" onclick="removeItem(${item.item_id})">
                    <i class="fa-solid fa-trash"></i> Rimuovi
                </button>
            </div>
            <div class="item-price">
                €${(item.price * item.quantity).toFixed(2)}
            </div>
        </div>
        `;
    });

    cartContainer.innerHTML = html;
}

function updateCartSummary(items) {
    const summaryContainer = document.getElementById('cartSummary');
    const subtotal = items.reduce((total, item) => total + (item.price * item.quantity), 0);
    const shipping = 5; // Fixed shipping cost
    const total = subtotal + shipping;

    const html = `
        <div class="summary-row">
            <span>Subtotale</span>
            <span>€${subtotal.toFixed(2)}</span>
        </div>
        <div class="summary-row">
            <span>Spedizione</span>
            <span>€${shipping.toFixed(2)}</span>
        </div>
        <div class="summary-row total">
            <span>Totale</span>
            <span>€${total.toFixed(2)}</span>
        </div>
    `;

    summaryContainer.innerHTML = html;
}

async function updateQuantity(cartItemId, newQuantity) {
    if (newQuantity < 1) return;

    try {
        const response = await fetch('http://localhost:8000/src/api/cart/read.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                cart_item_id: cartItemId,
                quantity: newQuantity
            })
        });

        if (response.ok) {
            loadCartItems(); // Reload cart to show updated quantities
        } else {
            const data = await response.json();
            throw new Error(data.error || 'Error updating quantity');
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
        alert('Error updating quantity. Please try again.');
    }
}

async function removeItem(cartItemId) {
    if (!confirm('Sei sicuro di voler rimuovere questo articolo dal carrello?')) {
        return;
    }

    try {
        const response = await fetch(`http://localhost:8000/src/api/cart/read.php?cart_item_id=${cartItemId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.ok) {
            loadCartItems(); // Reload cart to show remaining items
        } else {
            const data = await response.json();
            throw new Error(data.error || 'Error removing item');
        }
    } catch (error) {
        console.error('Error removing item:', error);
        alert('Error removing item. Please try again.');
    }
}

// Event Listeners
document.getElementById('continueShoppingBtn').addEventListener('click', () => {
    window.location.href = '@pages/products/';
});

document.getElementById('checkoutBtn').addEventListener('click', () => {
    window.location.href = '@pages/checkout/';
});

// Make functions available globally for onclick handlers
window.updateQuantity = updateQuantity;
window.removeItem = removeItem;

// Authentication check and cart loading
document.addEventListener('DOMContentLoaded', () => {
    const token = getToken();
    const loginPageUrl = '/src/pages/login';

    function isTokenExpired(token) {
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            const currentTime = Math.floor(Date.now() / 1000);
            return payload.exp < currentTime;
        } catch (e) {
            console.error('Invalid JWT:', e);
            return true;
        }
    }

    if (!token || isTokenExpired(token)) {
        window.location.href = loginPageUrl;
    } else {
        loadCartItems();
    }
});
