import "./style.scss";

document.addEventListener("scroll", () => {
  const header = document.querySelector("header");
  if (window.scrollY > 50) {
    header.classList.add("scrolled");
  } else {
    header.classList.remove("scrolled");
  }
});


const API_BASE_URL = 'your-api-base-url';
const ENDPOINTS = {
  worldCup: '/api/worldcup',
  leagues: '/api/leagues',
  teams: '/api/teams'
};

// Fetch data from API
async function fetchData(endpoint) {
  try {
    const response = await fetch(`${API_BASE_URL}${endpoint}`);
    if (!response.ok) throw new Error('Network response was not ok');
    return await response.json();
  } catch (error) {
    console.error('Error fetching data:', error);
    return null;
  }
}

// Update World Cup groups
async function updateWorldCupGroups() {
  const groupsData = await fetchData(ENDPOINTS.worldCup);
  if (!groupsData) return;

  const groups = document.querySelectorAll('.gruppo');
  groups.forEach((group, index) => {
    const groupLetter = String.fromCharCode(65 + index); // A, B, C, etc.
    const teams = groupsData[groupLetter] || [];
    
    const nationals = group.querySelectorAll('.nazionale');
    nationals.forEach((national, teamIndex) => {
      if (teams[teamIndex]) {
        national.textContent = teams[teamIndex].name;
        national.dataset.teamId = teams[teamIndex].id;
      }
    });
  });
}

// Update leagues and teams
async function updateLeagues() {
  const leaguesData = await fetchData(ENDPOINTS.leagues);
  if (!leaguesData) return;

  const leagueContainers = document.querySelectorAll('.lega');
  leagueContainers.forEach(async (container) => {
    const leagueLogo = container.querySelector('.logo');
    const leagueId = leagueLogo.alt.toLowerCase().replace(' ', '_');
    
    const teamsData = await fetchData(`${ENDPOINTS.teams}/${leagueId}`);
    if (!teamsData) return;

    const teamsList = container.querySelector('.squadre');
    teamsList.innerHTML = teamsData
      .map(team => `<li data-team-id="${team.id}">${team.name}</li>`)
      .join('');
  });
}

// Photo slideshow functionality
function initPhotoSlideshow() {
  const slideshow = document.querySelector('.photoslideshow');
  const slides = slideshow.querySelectorAll('div');
  let currentSlide = 0;

  function showNextSlide() {
    slides[currentSlide].style.display = 'none';
    currentSlide = (currentSlide + 1) % slides.length;
    slides[currentSlide].style.display = 'block';
  }

  // Initialize slides
  slides.forEach((slide, index) => {
    slide.style.display = index === 0 ? 'block' : 'none';
  });

  // Change slide every 5 seconds
  setInterval(showNextSlide, 5000);
}

// Marquee animation
// Initialize all dynamic features
async function initializePage() {
  await Promise.all([
    updateWorldCupGroups(),
    updateLeagues()
  ]);
  
  initPhotoSlideshow();
}

// Start when DOM is loaded
document.addEventListener('DOMContentLoaded', initializePage);

export { initializePage };