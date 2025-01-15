import "./style.scss";

document.addEventListener("DOMContentLoaded", async () => {
  const teamId = getTeamIdFromURL();
  const productContainer = document.querySelector(".prodotto-container");

  try {
    const data = await fetchTeamData(teamId);
    const allSizes = await fetchAllSizes();
    renderContent(data, teamId, productContainer, allSizes);
  } catch (error) {
    handleFetchError(error, teamId, productContainer);
  }
});

function getTeamIdFromURL() {
  return new URLSearchParams(window.location.search).get("id") || "1";
}

async function fetchTeamData(teamId) {
  console.log("Fetching data for team ID:", teamId);
  const response = await fetch(
    `http://localhost:8000/src/api/product/read.php?id=${teamId}`
  );
  console.log("Response status:", response.status);

  const data = await response.json();
  console.log("Received data:", data);
  if (!response.ok || !data || data.error) {
    throw new Error(data.error || "Errore nel caricamento dei dati");
  }
  return data;
}

async function fetchAllSizes() {
  console.log("Fetching all sizes");
  const response = await fetch(`http://localhost:8000/src/api/sizes/read.php`);
  console.log("Sizes response status:", response.status);

  const data = await response.json();
  console.log("All sizes data:", data);
  if (!response.ok || !data) {
    throw new Error("Errore nel caricamento delle taglie disponibili");
  }
  return data;
}

function renderContent(data, teamId, container, allSizes) {
  if (data.tshirts && data.tshirts.length > 0) {
    renderTeamTshirt(data, container, allSizes);
  } else {
    container.innerHTML = `
      <div class="error-message">
        <p>Nessuna t-shirt disponibile per il team selezionato.</p>
        <p>Team ID: ${teamId}</p>
      </div>`;
  }
}

function handleFetchError(error, teamId, container) {
  console.error("Fetch error:", error);
  container.innerHTML = `
    <div class="error-message">
      <p>Errore nel caricamento dei dati:</p>
      <p>${error.message}</p>
      <p>Team ID: ${teamId}</p>
    </div>`;
}

function renderVersionOptions(editionId, tshirts) {
  return tshirts
    .map(
      (tshirt) => `
        <input type="radio" id="version-${
          tshirt.tshirt_id
        }" name="versione" value="${tshirt.tshirt_id}" ${
        editionId === tshirt.tshirt_id ? "checked" : ""
      } />
        <label for="version-${tshirt.tshirt_id}">${tshirt.edition_id}</label>
      `
    )
    .join("");
}

function renderSizeOptions(sizes, allSizes) {
  console.log(allSizes);
  return allSizes
    .map((globalSize) => {
      const matchingSize = sizes.find(
        (size) => size.size_name === globalSize.name
      );
      const isDisabled = !matchingSize || globalSize.availability <= 0;

      return `
        <input type="radio" id="size-${globalSize.name}" name="Taglia"
            value="${globalSize.name}"
            data-item-id="${matchingSize ? matchingSize.item_id : ""}"
            ${isDisabled ? "disabled" : ""} />
        <label for="size-${globalSize.name}">
            ${globalSize.name}
        </label>
      `;
    })
    .join("");
}

let tshirts = [];

function renderTeamTshirt(teamData, container, allSizes) {
  tshirts = teamData.tshirts;
  const selectedTshirt =
    tshirts.find((t) => t.tshirt_id === teamData.edition_id) || tshirts[0];

  container.innerHTML = `
    <div class="prodotto-image">
      <h2>${teamData.team_name}</h2>
      <img id="tshirt-image" src="${selectedTshirt.image_url}" alt="Maglia ${
    teamData.team_name
  }" />
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
          <fieldset class="versione">
            <legend>Versione</legend>
            ${renderVersionOptions(selectedTshirt.tshirt_id, tshirts)}
          </fieldset>
        </div>
      </div>

      <div class="dettagli">
        <div class="dettagli-item">
          <label for="numero">Numero</label>
          <input type="number" id="numero" placeholder="1" />
        </div>
        <div class="dettagli-item">
          <label for="nome">Nome</label>
          <input type="text" id="nome" placeholder="Nome" />
        </div>
      </div>

      <div class="dettagli">
        <fieldset class="taglie" id="taglie-container">
          <legend>Taglia</legend>
          ${renderSizeOptions(selectedTshirt.sizes, allSizes)}
        </fieldset>
      </div>

      <div class="prezzo">
        <h4 for="prezzo">Prezzo: €${selectedTshirt.price}</h4>
      </div>

      <div class="azioni">
        <button class="secondary" onclick="addToCart('${
          selectedTshirt.tshirt_id
        }')">Aggiungi al carrello</button>
        <button class="primary" onclick="buyNow('${
          selectedTshirt.tshirt_id
        }')">Compra ora</button>
      </div>
    </div>`;

  setupEventListeners(allSizes);
}

function setupEventListeners(allSizes) {
  document.querySelectorAll('input[name="versione"]').forEach((input) => {
    input.addEventListener("change", (e) => handleVersionChange(e, allSizes));
  });

  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("not-available")) {
      e.preventDefault();
      alert("Taglia non disponibile");
    }
  });
}

function handleVersionChange(e, allSizes) {
  const selectedId = parseInt(e.target.value);
  const selectedTshirt = tshirts.find((t) => t.tshirt_id === selectedId);

  if (selectedTshirt) {
    document.getElementById("tshirt-image").src = selectedTshirt.image_url;
    document.getElementById("taglie-container").innerHTML = renderSizeOptions(
      selectedTshirt.sizes,
      allSizes
    );
  }
}

window.addToCart = async function (tshirtId) {
  try {
    const selectedSize = document.querySelector(
      'input[name="Taglia"]:checked'
    ).value;
    const quantity = document.getElementById("numero").value || 1;

    const selectedTshirt = tshirts.find(
      (t) => t.tshirt_id === parseInt(tshirtId)
    );
    const selectedSizeObj = selectedTshirt.sizes.find(
      (s) => s.size_name === selectedSize
    );

    validateCartInput(selectedSizeObj, quantity);

    await addToCartRequest(selectedSizeObj.item_id, quantity);
    showSuccessMessage("Prodotto aggiunto al carrello con successo!");
  } catch (error) {
    console.error("Error adding to cart:", error);
    showErrorMessage("Errore di connessione. Riprova più tardi.");
  }
};

function validateCartInput(selectedSizeObj, quantity) {
  if (!selectedSizeObj) throw new Error("Selected size not found");
  if (selectedSizeObj.availability < quantity)
    throw new Error(`Solo ${selectedSizeObj.availability} pezzi disponibili`);
}

async function addToCartRequest(itemId, quantity) {
  const response = await fetch(
    "http://localhost:8000/src/api/cart/create.php",
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
      },
      body: JSON.stringify({
        item_id: itemId,
        quantity: parseInt(quantity),
      }),
    }
  );

  const data = await response.json();
  if (!response.ok)
    throw new Error(data.error || "Errore durante l'aggiunta al carrello");
}

function showSuccessMessage(message) {
  const successMessage = document.createElement("div");
  successMessage.className = "success-message";
  successMessage.textContent = message;
  document.querySelector(".azioni").prepend(successMessage);
  setTimeout(() => successMessage.remove(), 3000);
}

function showErrorMessage(message) {
  const errorMessage = document.createElement("div");
  errorMessage.className = "error-message";
  errorMessage.textContent = message;
  document.querySelector(".azioni").prepend(errorMessage);
  setTimeout(() => errorMessage.remove(), 3000);
}

window.buyNow = function (tshirtId) {
  addToCart(tshirtId).then(() => {
    window.location.href = "/src/pages/cart/";
  });
};
