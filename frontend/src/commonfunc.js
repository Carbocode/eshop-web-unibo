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
export function getTokenRole(token) {
  try {
    const payload = JSON.parse(atob(token.split(".")[1])); // Decode JWT payload
    return payload.role;
  } catch (e) {
    console.error("Invalid JWT:", e);
    return "UNLOGGED";
  }
}

function isLoggedIn() {
  const token = getToken();
  return token && !isTokenExpired(token);
}

function updateAuthButtons() {
  const userIcon = document.querySelector(".fa-circle-user").parentElement;

  if (isLoggedIn()) {
    // Update user icon to go to profile
    userIcon.setAttribute("href", "/src/pages/profile/");
    userIcon.setAttribute("aria-label", "Vai al tuo profilo");
  } else {
    // Update user icon to go to login
    userIcon.setAttribute("href", "/src/pages/login/");
    userIcon.setAttribute("aria-label", "Accedi al tuo account");
  }
}

async function readCartCount() {
  try {
    // Effettua una richiesta POST all'API
    const response = await fetch(
      "http://localhost:8000/src/api/cart/number/read.php",
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

    const cart = document.querySelector(".fa-cart-shopping");

    if (data.error) {
      console.error("Errore dall'API:", data.error);
      cart.textContent = "";
    } else {
      // Aggiorna il contenuto dell'elemento HTML
      if (data.item_count > 0) cart.textContent = data.item_count;
      else cart.textContent = "";
    }
  } catch (error) {
    console.error("Errore durante la chiamata API:", error);
    document.querySelector(".fa-cart-shopping").textContent = "";
  }
}

export async function readNotificationsCount() {
  try {
    // Effettua una richiesta POST all'API
    const response = await fetch(
      "http://localhost:8000/src/api/notifications/number/read.php",
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

    const bell = document.querySelector(".fa-bell");

    if (data.error) {
      console.error("Errore dall'API:", data.error);
      bell.textContent = "";
    } else {
      // Aggiorna il contenuto dell'elemento HTML
      if (data.notifications_count > 0)
        bell.textContent = data.notifications_count;
      else bell.textContent = "";
    }
  } catch (error) {
    console.error("Errore durante la chiamata API:", error);
    document.querySelector(".fa-bell").textContent = "";
  }
}
const adminPages = ["/src/pages/manage/"];
const publicPages = [
  "/src/pages/home/",
  "/src/pages/login/",
  "/src/pages/register/",
  "/src/pages/products/",
];
const currentPath = new URL(window.location.href).pathname;
document.addEventListener("DOMContentLoaded", () => {
  adminPages.forEach((page) => {
    if (window.location.href.includes(page)) {
      if (getTokenRole(getToken()) != "ADMIN") {
        window.location.href = "/src/pages/home/";
      }
    }
  });
  let permits = publicPages.includes(currentPath);
  if (!permits && !isLoggedIn()) {
    console.log("Unauthorized access. Redirecting to home...");
    window.location.href = "/home"; // Redirect to home
  }
  updateAuthButtons();
  readCartCount();
  readNotificationsCount();
});
