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
            // Handle backend error or empty data
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
        <input type="radio" id="size-${size.size_name}" name="Taglia" value="${size.size_name}" ${index === 0 ? 'checked' : ''} />
        <label for="size-${size.size_name}">${size.size_name}</label>
    `).join('');
}

function renderTeamTshirt(teamData, container) {
    const tshirts = teamData.tshirts;
    let selectedTshirt = tshirts.find(t => t.tshirt_id === teamData.edition_id) || tshirts[0];

    const tshirtHTML = `
        <h3>${teamData.team_name}</h3>
        <div class="prodotto-image">
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
                    <label>Versione</label>
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
                <label>Taglia</label>
                <div class="taglie" id="taglie-container">
                    ${renderSizeOptions(selectedTshirt.sizes)}
                </div>
            </div>

            <div class="prezzo">
                <label>Prezzo: €<span id="tshirt-price">${selectedTshirt.price}</span></label>
            </div>

            <div class="azioni">
                <button class="secondary" onclick="addToCart('${selectedTshirt.tshirt_id}')">AGGIUNGI AL CARRELLO</button>
                <button class="primary" onclick="buyNow('${selectedTshirt.tshirt_id}')">COMPRA ORA</button>
            </div>
        </div>
    `;

    container.innerHTML = tshirtHTML;

    // Aggiungi evento per cambiare immagine, taglie e prezzo al cambio di versione
    document.querySelectorAll('input[name="versione"]').forEach(input => {
        input.addEventListener('change', (e) => {
            const selectedId = parseInt(e.target.value);
            const selectedTshirt = tshirts.find(t => t.tshirt_id === selectedId);

            if (selectedTshirt) {
                // Aggiorna immagine
                document.getElementById('tshirt-image').src = selectedTshirt.image_url;

                // Aggiorna taglie
                document.getElementById('taglie-container').innerHTML = renderSizeOptions(selectedTshirt.sizes);

                // Aggiorna prezzo
                document.getElementById('tshirt-price').textContent = selectedTshirt.price;
            }
        });
    });
}

// Funzioni di gestione carrello e acquisto (solo esempio)
window.addToCart = function(tshirtId) {
    const selectedSize = document.querySelector('input[name="Taglia"]:checked').value;
    alert(`T-shirt ${tshirtId} taglia ${selectedSize} aggiunta al carrello!`);
};

window.buyNow = function(tshirtId) {
    const selectedSize = document.querySelector('input[name="Taglia"]:checked').value;
    alert(`Acquisto immediato della T-shirt ${tshirtId} taglia ${selectedSize}!`);
};
