import "./style.scss";

document.addEventListener("scroll", () => {
  const header = document.querySelector("header");
  if (window.scrollY > 50) {
    header.classList.add("scrolled");
  } else {
    header.classList.remove("scrolled");
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const apiEndpoint = "http://localhost:8000/src/api/groups/read.php"; // Cambia l'URL con quello corretto per la tua API
  const container = document.querySelector(".griglianazionali");

  fetch(apiEndpoint)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Errore nella risposta dell'API");
      }
      return response.json();
    })
    .then((data) => {
      renderGroups(data, container);
    })
    .catch((error) => {
      console.error("Errore:", error);
      container.innerHTML = "<p>Errore nel caricamento dei dati.</p>";
    });
});

function renderGroups(groups, container) {
  groups.forEach((group) => {
    const groupDiv = document.createElement("div");
    groupDiv.classList.add("gruppo");

    const groupTitle = document.createElement("h3");
    groupTitle.textContent = group.group_name;
    groupDiv.appendChild(groupTitle);

    const nationsGrid = document.createElement("div");
    nationsGrid.classList.add("grigliagruppo");

    group.countries.forEach((country) => {
      const nationDiv = document.createElement("div");
      nationDiv.classList.add("nazionale");

      // Aggiungi immagine bandiera
      const flagImage = document.createElement("img");
      flagImage.src = country.country_flag;
      flagImage.alt = `${country.country_name} flag`;
      flagImage.style.width = "50px"; // Stile opzionale per dimensionare l'immagine
      nationDiv.appendChild(flagImage);

      // Aggiungi nome del paese
      const countryName = document.createElement("div");
      countryName.textContent = country.country_name;
      nationDiv.appendChild(countryName);

      nationsGrid.appendChild(nationDiv);
    });

    groupDiv.appendChild(nationsGrid);
    container.appendChild(groupDiv);
  });
}

document.addEventListener("DOMContentLoaded", () => {
  const apiEndpoint = "http://localhost:8000/src/api/leagues/read.php"; // Cambia l'URL con quello corretto per la tua API
  const container = document.querySelector(".leghe-container");

  fetch(apiEndpoint)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Errore nella risposta dell'API");
      }
      return response.json();
    })
    .then((data) => {
      renderLeagues(data, container);
    })
    .catch((error) => {
      console.error("Errore:", error);
      container.innerHTML = "<p>Errore nel caricamento dei dati.</p>";
    });
});

function renderLeagues(leagues, container) {
  leagues.forEach((league) => {
    const leagueDiv = document.createElement("div");
    leagueDiv.classList.add("lega");

    // Aggiungi il link e il logo della lega
    const leagueLink = document.createElement("a");
    leagueLink.href = `/src/pages/products/?id=${league.league_id}`; // Cambia con il percorso reale per la lega
    leagueLink.title = `Vai alla pagina della lega ${league.league_name}`;

    const leagueLogo = document.createElement("img");
    leagueLogo.src = league.league_logo;
    leagueLogo.alt = `${league.league_name} Logo`;
    leagueLogo.classList.add("logo");

    leagueLink.appendChild(leagueLogo);
    leagueDiv.appendChild(leagueLink);

    // Aggiungi la lista delle squadre
    const teamList = document.createElement("ul");
    teamList.classList.add("squadre");

    league.teams.forEach((team) => {
      const teamItem = document.createElement("li");

      // Crea il link per la squadra
      const teamLink = document.createElement("a");
      teamLink.href = `/src/pages/product/?id=${team.team_id}`; // Cambia l'URL con il corretto percorso per la squadra
      teamLink.textContent = team.team_name;

      teamItem.appendChild(teamLink);
      teamList.appendChild(teamItem);
    });

    leagueDiv.appendChild(teamList);
    container.appendChild(leagueDiv);
  });
}
