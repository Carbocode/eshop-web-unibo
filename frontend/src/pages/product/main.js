import "./style.scss";

document.addEventListener('DOMContentLoaded', async () => {
    const teamId = getTeamIdFromURL(); // Recupera l'id_team dall'URL
    const prodottoContainer = document.querySelector('.prodotto-container');

    try {
        const response = await fetch(`http://localhost:8000/src/api/product/read.php?id_team=${teamId}`);
        const tshirt = await response.json(); 

        if (response.ok && tshirt) {
            renderTshirt(tshirt, prodottoContainer);
        } else {
            prodottoContainer.innerHTML = `<p>Nessuna t-shirt disponibile per il team selezionato.</p>`;
        }
    } catch (error) {
        prodottoContainer.innerHTML = `<p>Errore nel caricamento dei dati: ${error.message}</p>`;
    }
});

// Funzione per recuperare l'id_team dall'URL
function getTeamIdFromURL() {
    const params = new URLSearchParams(window.location.search);
    return params.get('id_team') || 1; // Default a 1 se non specificato
}

// Funzione per renderizzare la t-shirt
function renderTshirt(tshirt, container) {
    console.log(tshirt);
    const tshirtHTML = `
        <div class="prodotto-card">
            <div class="prodotto-image">
                <img src="${tshirt.image_url}" alt="T-shirt ${tshirt.team.team_name}" />
            </div>
            <div class="prodotto-dettagli">
                <h3>${tshirt.team.team_name}</h3>
                <p>Edizione: ${tshirt.edition.year}</p>
                <div class="dettagli-item">
                    <label>Versione</label>
                    <div class="versione">
                        ${renderVersionOptions(tshirt.versions)}
                    </div>
                </div>
                <p>Taglia: ${tshirt.size}</p>
                <p>Prezzo: €${tshirt.price.toFixed(2)}</p>
                <p>Disponibilità: ${tshirt.availability}</p>
                <div class="azioni">
                    <button class="secondary" onclick="addToCart(${tshirt.tshirt_id})">AGGIUNGI AL CARRELLO</button>
                    <button class="primary" onclick="buyNow(${tshirt.tshirt_id})">COMPRA ORA</button>
                </div>
            </div>
        </div>
    `;
    container.innerHTML = tshirtHTML; 

    // Aggiunge l'evento di cambio versione
    const versionInputs = document.querySelectorAll('input[name="versione"]');
    versionInputs.forEach((input) =>
        input.addEventListener('change', () => updateVersionDetails(versions, input.value))
    );
}


// Funzione per rendere le opzioni di versione dinamiche
function renderVersionOptions(versions) {
    return versions
        .map(
            (version, index) => `
            <input type="radio" id="version-${index}" name="versione" value="${index}" ${index === 0 ? 'checked' : ''} />
            <label for="version-${index}">${version.name}</label>
        `
        )
        .join('');
}

// Funzione per aggiornare i dettagli in base alla versione selezionata
function updateVersionDetails(versions, selectedIndex) {
    const selectedVersion = versions[selectedIndex];
    document.getElementById('prezzo').innerText = `Prezzo: €${selectedVersion.price.toFixed(2)}`;
    document.getElementById('disponibilita').innerText = `Disponibilità: ${selectedVersion.availability}`;
}

// Funzione per aggiungere al carrello (placeholder)
function addToCart(tshirtId) {
    alert(`T-shirt ${tshirtId} aggiunta al carrello!`);
}

// Funzione per comprare subito (placeholder)
function buyNow(tshirtId) {
    alert(`Acquisto immediato della T-shirt ${tshirtId}!`);
}
