import "./style.scss";

document.addEventListener('DOMContentLoaded', async () => {
    const teamId = getTeamIdFromURL();
    const productContainer = document.querySelector('.prodotto-container');

    console.log('Fetching data for team ID:', teamId); 

    try {
        const response = await fetch(`http://localhost:8000/src/api/product/read.php?id=${teamId}`);
        console.log('Response status:', response.status); 
        
        const data = await response.json();
        console.log('Received data:', data); 

        if (response.ok && data && !data.error) {
            if (data.tshirts && data.tshirts.length > 0) {
                renderTeamTshirt(data, productContainer);
            } else {
                productContainer.innerHTML = `
                    <div class="error-message">
                        <p>Nessuna t-shirt disponibile per il team selezionato.</p>
                        <p>Team ID: ${teamId}</p>
                    </div>`;
            }
        } else {
            productContainer.innerHTML = `
                <div class="error-message">
                    <p>Errore nel caricamento dei dati:</p>
                    <p>${data.error || 'Nessuna t-shirt disponibile'}</p>
                    <p>Team ID: ${teamId}</p>
                    ${data.debug ? `<pre>${JSON.stringify(data.debug, null, 2)}</pre>` : ''}
                </div>`;
        }
    } catch (error) {
        console.error('Fetch error:', error);
        productContainer.innerHTML = `
            <div class="error-message">
                <p>Errore nel caricamento dei dati:</p>
                <p>${error.message}</p>
                <p>Team ID: ${teamId}</p>
            </div>`;
    }
});

function getTeamIdFromURL() {
    const params = new URLSearchParams(window.location.search);
    return params.get('id') || '1'; 
}

function renderVersionOptions(editionId, tshirts) {
    return tshirts.map(tshirt => `
        <input type="radio" id="version-${tshirt.tshirt_id}" name="versione" value="${tshirt.tshirt_id}" ${editionId === tshirt.tshirt_id ? 'checked' : ''} />
        <label for="version-${tshirt.tshirt_id}">${tshirt.edition_id}</label>
    `).join('');
}

function renderSizeOptions(sizes) {
    return sizes.map((size, index) => `
        <input type="radio" id="size-${size.size_name}" name="Taglia"
            value="${size.size_name}"
            data-item-id="${size.item_id}"
            ${index === 0 && size.availability > 0 ? 'checked' : ''}
            ${size.availability > 0 ? '' : 'class="not-available"'} />
        <label for="size-${size.size_name}">
            ${size.size_name}
            ${size.availability > 0 ? '' : ' (Non disponibile)'}
        </label>
    `).join('');
}

let tshirts = [];

function renderTeamTshirt(teamData, container) {
    tshirts = teamData.tshirts;
    let selectedTshirt = tshirts.find(t => t.tshirt_id === teamData.edition_id) || tshirts[0];

    const tshirtHTML = `
        <div class="prodotto-image">
        <h2>${teamData.team_name}</h2>
            <img id="tshirt-image" src="${selectedTshirt.image_url}" alt="Maglia ${teamData.team_name}" />
        </div>
        <div class="prodotto-dettagli">
            <div class="dettagli">
                <div class="dettagli-item">
                    <label for="annata">Annata</label>
                    <select id="annata">
                        <option>${selectedTshirt.edition_year}</option>
                    </select>
                </div>
                <div class="dettagli-item">
                    <label for="versione">Versione</label>
                    <div class="versione">
                        ${renderVersionOptions(teamData.edition_id, tshirts)}
                    </div>
                </div>
            </div>

            <div class="dettagli">
                <div class="dettagli-item">
                    <label for="numero">Numero</label>
                    <input type="number" id="numero" placeholder="1" />
                </div>
                <div class="dettagli-item">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" placehoder="Nome" />
                </div>
            </div>

            <div class="dettagli">
                <label for="taglia">Taglia</label>
                <div class="taglie" id="taglie-container">
                    ${renderSizeOptions(selectedTshirt.sizes)}
                </div>
            </div>

            <div class="prezzo">
                <label for="prezzo" >Prezzo: €<span id="tshirt-price">${selectedTshirt.price}</span></label>
            </div>

            <div class="azioni">
                <button class="secondary" onclick="addToCart('${selectedTshirt.tshirt_id}')">AGGIUNGI AL CARRELLO</button>
                <button class="primary" onclick="buyNow('${selectedTshirt.tshirt_id}')">COMPRA ORA</button>
            </div>
        </div>
    `;

    container.innerHTML = tshirtHTML;

    document.querySelectorAll('input[name="versione"]').forEach(input => {
        input.addEventListener('change', (e) => {
            const selectedId = parseInt(e.target.value);
            const selectedTshirt = tshirts.find(t => t.tshirt_id === selectedId);

            if (selectedTshirt) {
                document.getElementById('tshirt-image').src = selectedTshirt.image_url;
                document.getElementById('taglie-container').innerHTML = renderSizeOptions(selectedTshirt.sizes);
                document.getElementById('tshirt-price').textContent = selectedTshirt.price;
            }
        });
    });

    // Gestione alert per taglie non disponibili
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('not-available')) {
            e.preventDefault();
            alert('Taglia non disponibile');
        }
    });
}

window.addToCart = async function(tshirtId) {
    try {
        const selectedSize = document.querySelector('input[name="Taglia"]:checked').value;
        const quantity = document.getElementById('numero').value || 1;

        const selectedTshirt = tshirts.find(t => t.tshirt_id === parseInt(tshirtId));
        if (!selectedTshirt) {
            throw new Error('Selected t-shirt not found');
        }

        const selectedSizeObj = selectedTshirt.sizes.find(s => s.size_name === selectedSize);
        if (!selectedSizeObj) {
            throw new Error('Selected size not found');
        }

        if (!selectedSizeObj.item_id) {
            throw new Error('No warehouse item found for this combination');
        }

        if (selectedSizeObj.availability < quantity) {
            throw new Error(`Solo ${selectedSizeObj.availability} pezzi disponibili`);
        }

        const response = await fetch('http://localhost:8000/src/api/cart/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
            },
            body: JSON.stringify({
                item_id: selectedSizeObj.item_id,
                quantity: parseInt(quantity)
            })
        });

        const data = await response.json();

        if (response.ok) {
            const successMessage = document.createElement('div');
            successMessage.className = 'success-message';
            successMessage.textContent = 'Prodotto aggiunto al carrello con successo!';
            document.querySelector('.azioni').prepend(successMessage);
            setTimeout(() => successMessage.remove(), 3000);
        } else {
            const errorMessage = document.createElement('div');
            errorMessage.className = 'error-message';
            errorMessage.textContent = data.error || 'Errore durante l\'aggiunta al carrello';
            document.querySelector('.azioni').prepend(errorMessage);
            setTimeout(() => errorMessage.remove(), 3000);
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        const errorMessage = document.createElement('div');
        errorMessage.className = 'error-message';
        errorMessage.textContent = 'Errore di connessione. Riprova più tardi.';
        document.querySelector('.azioni').prepend(errorMessage);
        setTimeout(() => errorMessage.remove(), 3000);
    }
};

window.buyNow = function(tshirtId) {
    const selectedSize = document.querySelector('input[name="Taglia"]:checked').value;
    addToCart(tshirtId);
    window.location.href = '/src/pages/cart/';
};
