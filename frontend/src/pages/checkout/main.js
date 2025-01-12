import "./style.scss";
const token = localStorage.getItem('token'); // Assuming token is stored in localStorage

async function loadOrderSummary() {
    try {
        //parseJwt(token).sub non va jwt
        const response = await fetch('http://localhost:8000/src/checkout/process.php?'+new URLSearchParams({
            customer_id: 1,
        }));
        const data = await response.json();
        
        if (response.ok) {
            displayOrderSummary(data);
        } else {
            throw new Error(data);
        }
    } catch (error) {
        console.error('Error loading order summary:', error);
    }
}

function displayOrderSummary(data) {
    const summaryContainer = document.getElementById('orderSummary');
    const totalAmountSpan = document.getElementById('totalAmount');
    
    let html = '<h2>Ordine</h2>';
    
    // Display items
    html += data.map(item => `
        <div class="summary-item">
            <img src="${item.image_url}" alt="${item.team_name}">
            <div class="summary-details">
                <div>${item.team_name} - ${item.edition_name}</div>
                <div>Size: ${item.size}</div>
                <div>Quantity: ${item.quantity}</div>
            </div>
            <div class="summary-price">€${item.subtotal}</div>
        </div>
    `).join('');

    // Display totals
    
    html += `
        <div class="totals">
            <div class="total-row">
                <span>Prezzo Articoli</span>
                <span>€1</span>
            </div>
            <div class="total-row">
                <span>Prezzo Spedizione</span>
                <span>€5</span>
            </div>
            <div class="total-row final">
                <span>Prezzo Totale</span>
                <span>€${data.reduce((acc, item) => acc + item.price, 0)}</span>
            </div>
        </div>
    `;

    summaryContainer.innerHTML = html;
    totalAmountSpan.textContent = (data.summary.total + 5).toFixed(2);
}

function validateForm(formData) {
    const { cardNumber, cardExpiry, cardCvv, cardName, address, postalCode, phone } = formData;

    // Validate card number (16 digits)
    if (!/^\d{16}$/.test(cardNumber)) {
        return 'Invalid card number. Please enter a 16-digit card number.';
    }

    // Validate card expiry (MM/YY format)
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(cardExpiry)) {
        return 'Invalid card expiry date. Please use the MM/YY format.';
    }

    // Validate CVV (3 or 4 digits)
    if (!/^\d{3,4}$/.test(cardCvv)) {
        return 'Invalid CVV. Please enter a 3 or 4-digit CVV.';
    }

    // Validate card name (non-empty)
    if (!cardName.trim()) {
        return 'Cardholder name is required.';
    }

    // Validate address (non-empty)
    if (!address.trim()) {
        return 'Address is required.';
    }

    // Validate postal code (5 digits)
    if (!/^\d{5}$/.test(postalCode)) {
        return 'Invalid postal code. Please enter a 5-digit postal code.';
    }

    // Validate phone number (10 digits)
    if (!/^\d{10}$/.test(phone)) {
        return 'Invalid phone number. Please enter a 10-digit phone number.';
    }

    return null; // No errors
}

document.getElementById('checkoutForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        cardNumber: document.getElementById('cardNumber').value,
        cardExpiry: document.getElementById('cardExpiry').value,
        cardCvv: document.getElementById('cardCvv').value,
        cardName: document.getElementById('cardName').value,
        address: document.getElementById('address').value,
        postalCode: document.getElementById('postalCode').value,
        phone: document.getElementById('phone').value,
        id: 1 //IDUtente, temporaneo
    };

    // Validate form data
    const validationError = validateForm(formData);
    if (validationError) {
        alert(validationError);
        return;
    }

    try {
        const response = await fetch('http://localhost:8000/src/checkout/process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();
        
        if (response.ok) {
            alert('Order placed successfully!');
            window.location.href = '/home';
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        alert('Error processing checkout: ' + error.message);
    }
});
function parseJwt (token) {
    var base64Url = token.split('.')[1];
    var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    var jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));

    return JSON.parse(jsonPayload);
}
// Load order summary when page loads
loadOrderSummary();
