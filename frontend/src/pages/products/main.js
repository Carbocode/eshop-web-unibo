import "./style.scss";

document.addEventListener("DOMContentLoaded", () => {
  const leagueId = getLeagueIdFromURL();
  const apiEndpoint = `http://localhost:8000/src/api/product/read_one.php?id=${leagueId}`;

  if (!leagueId) {
    showError("League ID not found in the URL.");
    return;
  }

  // Effettua la richiesta all'API per ottenere le squadre e una maglietta per squadra
  fetch(apiEndpoint)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Failed to fetch teams for the league");
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
      console.error("Error fetching teams:", error);
      showError("An error occurred while fetching teams.");
    });
});

// Funzione per ottenere il league_id dall'URL
function getLeagueIdFromURL() {
  const params = new URLSearchParams(window.location.search);
  return params.get("id");
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
            : `<p class="no-tshirt">Nessuna maglietta disponibile</p>`
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
  url; // Effettua il redirect
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
