import "./style.scss";

document.addEventListener('DOMContentLoaded', async () => {
    const teamId = getTeamIdFromURL();
    const productContainer = document.querySelector('.prodotto-container');

    console.log('Fetching data for team ID:', teamId); // Debug log

    try {
        const response = await fetch(`http://localhost:8000/src/api/product/read.php?id=${teamId}`);
        console.log('Response status:', response.status); // Debug log
        
        const data = await response.json();
        console.log('Received data:', data); // Debug log

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

function renderTeamTshirt(teamData, container) {
    const tshirt = teamData.tshirts[0]; // Seleziona solo la prima t-shirt
    const tshirtHTML = `
        <div class="prodotto-image">
            <img src="${tshirt.image_url}" alt="T-shirt ${teamData.team_name}" />
        </div>
        <div class="prodotto-dettagli">
            <div class="dettagli">
                <div class="dettagli-item">
                    <label for="annata">Annata</label>
                    <select id="annata">
                        <option>${tshirt.edition_year}</option>
                    </select>
                </div>
                <div class="dettagli-item">
                    <label>Versione</label>
                    <div class="versione">
                        ${renderVersionOptions(tshirt.edition_id)}
                    </div>
                </div>
            </div>

            <div class="dettagli">
                <div class="dettagli-item">
                    <label for="numero">Numero</label>
                    <input type="number" id="numero" value="69" />
                </div>
                <div class="dettagli-item">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" value="Bossetti" />
                </div>
            </div>

            <div class="dettagli">
                <label>Taglia</label>
                <div class="taglie">
                    ${renderSizeOptions(tshirt.sizes)}
                </div>
            </div>

            <div class="azioni">
                <button class="secondary" onclick="addToCart('${tshirt.tshirt_id}')">AGGIUNGI AL CARRELLO</button>
                <button class="primary" onclick="buyNow('${tshirt.tshirt_id}')">COMPRA ORA</button>
            </div>
        </div>
    `;

    container.innerHTML = tshirtHTML; // Renderizza solo una t-shirt
}

function renderVersionOptions(editionId) {
    const versions = [1, 2, 3]; // Esempio di versioni, puoi sostituirle con quelle reali
    return versions.map(version => `
        <input type="radio" id="${version}" name="versione" value="${version}" ${editionId === version ? 'checked' : ''} />
        <label for="${version}">${version}</label>
    `).join('');
}

function renderSizeOptions(sizes) {
    return sizes.map((size, index) => `
        <input type="radio" id="${size.size_name}" name="Taglia" value="${size.size_name}" ${index === 0 ? 'checked' : ''} />
        <label for="${size.size_name}">${size.size_name}</label>
    `).join('');
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
