import "./style.scss";

const API_URL = 'http://localhost/elprimerofootballer/cart_items.php'; // URL del tuo backend PHP

// Funzione per ottenere gli articoli del carrello
async function getCartItems(customerId) {
    try {
        const response = await fetch(`${API_URL}?customer_id=${customerId}`);
        const data = await response.json();

        if (response.ok) {
            renderCartItems(data); // Chiama la funzione per visualizzare gli articoli
        } else {
            console.error('Errore:', data.error || data.message);
        }
    } catch (error) {
        console.error('Errore durante il recupero degli articoli:', error);
    }
}

// Funzione per rimuovere un articolo dal carrello
async function deleteCartItem(cartItemId, customerId) {
    try {
        const response = await fetch(`${API_URL}?cart_item_id=${cartItemId}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (response.ok) {
            console.log('Articolo rimosso dal carrello:', data.message);
            getCartItems(customerId); // Aggiorna il carrello dopo la rimozione
        } else {
            console.error('Errore:', data.error || data.message);
        }
    } catch (error) {
        console.error('Errore durante la rimozione dell\'articolo:', error);
    }
}

// Funzione per visualizzare gli articoli del carrello in HTML
function renderCartItems(cartItems) {
    const cartContainer = document.querySelector('main');

    // Svuota il contenuto esistente
    cartContainer.innerHTML = '<h1>Carrello</h1>';

    if (cartItems.length === 0) {
        cartContainer.innerHTML += '<p>Il carrello è vuoto</p>';
        return;
    }

    cartItems.forEach(item => {
        const cartItemElement = document.createElement('div');
        cartItemElement.className = 'item-carrello';

        cartItemElement.innerHTML = `
            <img src="${item.tshirt.image_url}" alt="Maglia ${item.tshirt.team_name}">
            <div class="item-dettagli">
                <h4>${item.tshirt.team_name}</h4>
                <p>Anno ${item.edition.year}</p>
                <p>Numero ${item.tshirt.number}</p>
                <p>Versione ${item.edition.description}</p>
                <p>Taglia ${item.size}</p>
                <p>Nome ${item.customer_name}</p>
            </div>
            <button class="bottone-rimuovi" onclick="deleteCartItem(${item.cart_item_id}, ${item.customer_id})">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;

        cartContainer.appendChild(cartItemElement);
        cartContainer.innerHTML += '<hr>';
    });
}

// Esegui il caricamento del carrello al caricamento della pagina
document.addEventListener('DOMContentLoaded', () => {
    const customerId = 1; // ID del cliente (da modificare dinamicamente)
    getCartItems(customerId);
});

