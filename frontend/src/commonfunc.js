const method = "Storage";

function getCookies(name) {
  var name = name + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(";");
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == " ") {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function readTokenFromStorage() {
  return localStorage.getItem("auth_token");
}

export function getToken() {
  if (method == "Cookies") {
    return getCookies("auth_token");
  }
  return readTokenFromStorage();
}

function isTokenExpired(token) {
  try {
    const payload = JSON.parse(atob(token.split(".")[1])); // Decode JWT payload
    const currentTime = Math.floor(Date.now() / 1000); // Current time in seconds
    return payload.exp < currentTime; // Check expiration
  } catch (e) {
    console.error("Invalid JWT:", e);
    return true; // Treat invalid token as expired
  }
}

function isLoggedIn() {
  const token = getToken();
  return token && !isTokenExpired(token);
}

function logout() {
  localStorage.removeItem('auth_token');
  window.location.href = '/src/pages/home/';
}

function updateAuthButtons() {
  const userIcon = document.querySelector('.fa-circle-user').parentElement;
  let logoutButton = document.querySelector('.logout-button');
  
  if (isLoggedIn()) {
    // Update user icon to go to profile
    userIcon.setAttribute('href', '/src/pages/profile/');
    userIcon.setAttribute('aria-label', 'Vai al tuo profilo');
    
    // Add logout button if not present
    if (!logoutButton) {
      const nav = document.querySelector('nav');
      logoutButton = document.createElement('a');
      logoutButton.className = 'logout-button';
      logoutButton.setAttribute('aria-label', 'Logout dal tuo account');
      logoutButton.innerHTML = '<i class="fa-solid fa-sign-out"></i>';
      logoutButton.addEventListener('click', logout);
      // Insert before the cart icon (last element)
      nav.insertBefore(logoutButton, nav.lastElementChild);
    }
  } else {
    // Update user icon to go to login
    userIcon.setAttribute('href', '/src/pages/login/');
    userIcon.setAttribute('aria-label', 'Accedi al tuo account');
    
    // Remove logout button if present
    if (logoutButton) {
      logoutButton.remove();
    }
  }
}

async function readCartCount() {
  try {
    // Effettua una richiesta POST all'API
    const response = await fetch(
      "https://localhost:8000/src/api/cart/number/read.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`, // Usa il token di autenticazione
        },
        body: JSON.stringify({}), // Può essere vuoto in base alla configurazione dell'API
      }
    );

    if (!response.ok) {
      throw new Error("Errore durante la richiesta all'API");
    }

    const data = await response.json();

    if (data.error) {
      console.error("Errore dall'API:", data.error);
      document.querySelector(".fa-cart-shopping").textContent = "N/A";
    } else {
      // Aggiorna il contenuto dell'elemento HTML
      document.querySelector(".fa-cart-shopping").textContent = data.item_count;
    }
  } catch (error) {
    console.error("Errore durante la chiamata API:", error);
    document.querySelector(".fa-cart-shopping").textContent = "N/A";
  }
}

async function readNotificationsCount() {
  try {
    // Effettua una richiesta POST all'API
    const response = await fetch(
      "https://localhost:8000/src/api/notifications/number/read.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`, // Usa il token di autenticazione
        },
        body: JSON.stringify({}), // Può essere vuoto in base alla configurazione dell'API
      }
    );

    if (!response.ok) {
      throw new Error("Errore durante la richiesta all'API");
    }

    const data = await response.json();

    if (data.error) {
      console.error("Errore dall'API:", data.error);
      document.getElementById(".fa-circle-user").textContent = "N/A";
    } else {
      // Aggiorna il contenuto dell'elemento HTML
      document.getElementById(".fa-circle-user").textContent = data.item_count;
    }
  } catch (error) {
    console.error("Errore durante la chiamata API:", error);
    document.getElementById(".fa-circle-user").textContent = "N/A";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  updateAuthButtons();
  readCartCount();
  readNotificationsCount();
});

