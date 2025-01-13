import "./style.scss";

document.addEventListener("DOMContentLoaded", () => {
  const { id, type } = getParamsFromURL();
  const apiEndpoint = `http://localhost:8000/src/api/product/read_one.php?id=${id}&type=${type}`;

  // Effettua la richiesta all'API per ottenere i dati
  fetch(apiEndpoint)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Failed to fetch data");
      }
      return response.json();
    })
    .then((data) => {
      if (data.error) {
        showError(data.error);
      } else {
        populateTeams(data);
      }
    })
    .catch((error) => {
      console.error("Error fetching data:", error);
      showError("An error occurred while fetching data.");
    });
});

document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("team-search");
  const teamsContainer = document.querySelector(".teams-container");

  searchInput.addEventListener("input", () => {
    const query = searchInput.value.toLowerCase();
    const teams = teamsContainer.querySelectorAll(".team");

    teams.forEach((team) => {
      const teamName = team.querySelector("h3").textContent.toLowerCase();
      if (teamName.includes(query)) {
        team.style.display = "block";
      } else {
        team.style.display = "none";
      }
    });
  });
});

// Funzione per ottenere i parametri dall'URL
function getParamsFromURL() {
  const params = new URLSearchParams(window.location.search);
  return {
    id: params.get("id") ?? "",
    type: params.get("type") ?? "",
  };
}

// Funzione per popolare le squadre e le magliette nel contenitore
function populateTeams(teams) {
  const container = document.querySelector(".teams-container");
  container.innerHTML = ""; // Pulisce il contenitore

  teams.forEach((team) => {
    const teamElement = document.createElement("div");
    teamElement.classList.add("team");
    teamElement.setAttribute("data-team-id", team.team_id); // Attributo per identificare il team

    teamElement.innerHTML = `
      <img src="${team.tshirt?.image_url || team.team_logo}" alt="Maglietta ${
      team.team_name
    }" class="tshirt-image" />
      <div class="team-info">
        <h3>${team.team_name}</h3>
        ${
          team.tshirt && team.tshirt.tshirt_id
            ? `<p class="tshirt-price">${formatCurrency(team.tshirt.price)}</p>`
            : `<p class="no-tshirt" style="min-width: 30px;">N/A</p>`
        }
      </div>`;

    // Aggiungi un evento per il redirect al click
    teamElement.addEventListener("click", () => {
      redirectToTeamPage(team.team_id);
    });

    container.appendChild(teamElement);
  });
}

// Funzione per il redirect alla pagina del team
function redirectToTeamPage(teamId) {
  window.location.href = `/src/pages/product/?id=${teamId}`;
}

// Funzione per formattare una valuta
function formatCurrency(value) {
  return new Intl.NumberFormat("it-IT", {
    style: "currency",
    currency: "EUR",
  }).format(value || 0);
}

// Funzione per mostrare errori
function showError(message) {
  const container = document.querySelector(".teams-container");
  container.innerHTML = `<p class="error">${message}</p>`;
}
