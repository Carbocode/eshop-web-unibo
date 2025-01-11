import "./style.scss";

document.addEventListener('DOMContentLoaded', async () => {
    const teamId = getTeamIdFromURL(); // Recupera l'id_team dall'URL
    const prodottoContainer = document.querySelector('.prodotto-container');
  
    try {
      const response = await fetch(`http://localhost:8000/src/product/read.php?id_team=${teamId}`);
      const tshirts = await response.json();
  
      if (tshirts.error) {
        prodottoContainer.innerHTML = `<p>Errore: ${tshirts.error}</p>`;
        return;
      }
  
      if (tshirts.message) {
        prodottoContainer.innerHTML = `<p>${tshirts.message}</p>`;
        return;
      }
  
      // Popola i dati delle t-shirt dinamicamente
      renderTshirts(tshirts, prodottoContainer);
    } catch (error) {
      prodottoContainer.innerHTML = `<p>Errore nel caricamento dei dati: ${error.message}</p>`;
    }
  });
  
  // Funzione per recuperare l'id_team dall'URL
  function getTeamIdFromURL() {
    const params = new URLSearchParams(window.location.search);
    return params.get('id_team') || 1; // Default a 1 se non specificato
  }
  
  // Funzione per renderizzare le t-shirt
  function renderTshirts(tshirts, container) {
    container.innerHTML = ''; // Rimuove i contenuti statici
  
    tshirts.forEach(tshirt => {
      const tshirtHTML = `
        <div class="prodotto-image">
          <img src="${tshirt.image_url}" alt="T-shirt ${tshirt.team.team_name}" />
        </div>
        <div class="prodotto-dettagli">
          <h3>${tshirt.team.team_name}</h3>
  
          <div class="dettagli">
            <div class="dettagli-item">
              <label for="annata">Annata</label>
              <select id="annata">
                <option>${tshirt.edition.year}</option>
              </select>
            </div>
            <div class="dettagli-item">
              <label>Versione</label>
              <div class="versione">
                ${renderVersionOptions(tshirt.edition.versions)}
              </div>
            </div>
          </div>
  
          <div class="dettagli">
            <div class="dettagli-item">
              <label for="numero">Numero</label>
              <input type="number" id="numero" value="10" />
            </div>
            <div class="dettagli-item">
              <label for="nome">Nome</label>
              <input type="text" id="nome" value="Tuo Nome" />
            </div>
          </div>
  
          <div class="dettagli">
            <label>Taglia</label>
            <div class="taglie">
              ${renderSizeOptions(tshirt.sizes)}
            </div>
          </div>
  
          <p>Prezzo: €${tshirt.price}</p>
          <p>Quantità disponibile: ${tshirt.stock_quantity}</p>
  
          <div class="azioni">
            <button class="secondary" onclick="addToCart(${tshirt.tshirt_id})">AGGIUNGI AL CARRELLO</button>
            <button class="primary" onclick="buyNow(${tshirt.tshirt_id})">COMPRA ORA</button>
          </div>
        </div>
      `;
      const tshirtElement = document.createElement('div');
      tshirtElement.classList.add('tshirt-item');
      tshirtElement.innerHTML = tshirtHTML;
      container.appendChild(tshirtElement);
    });
  }
  
  // Funzione per rendere le opzioni di versione dinamiche
  function renderVersionOptions(versions) {
    if (!versions || versions.length === 0) {
      return '<p>Nessuna versione disponibile</p>';
    }
    return versions
      .map((version, index) => `
        <input type="radio" id="version-${index}" name="versione" value="${version}" ${index === 0 ? 'checked' : ''} />
        <label for="version-${index}">${version}</label>
      `)
      .join('');
  }
  
  
  // Funzione per rendere le opzioni di taglia dinamiche
  function renderSizeOptions(sizes) {
    if (!sizes || sizes.length === 0) {
      return '<p>Nessuna taglia disponibile</p>';
    }
    return sizes
      .map(size => `
        <input type="radio" id="size-${size}" name="Taglia" value="${size}" ${size === 'M' ? 'checked' : ''} />
        <label for="size-${size}">${size}</label>
      `)
      .join('');
  }
  
  
  // Funzione per aggiungere al carrello (placeholder)
  function addToCart(tshirtId) {
    alert(`T-shirt ${tshirtId} aggiunta al carrello!`);
  }
  
  // Funzione per comprare subito (placeholder)
  function buyNow(tshirtId) {
    alert(`Acquisto immediato della T-shirt ${tshirtId}!`);
  }