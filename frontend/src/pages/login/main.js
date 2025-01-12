import "./style.scss";

document
  .getElementById("login-form")
  .addEventListener("submit", async function (event) {
    event.preventDefault(); // Impedisce il comportamento predefinito del form

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    try {
      const response = await fetch(
        "http://localhost:8000/src/api/accounts/session/create.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ email, password }),
        }
      );

      if (response.ok) {
        const result = await response.json();
        // Imposta un cookie chiamato "auth_token" che scade in 7 giorni
        const d = new Date();
        d.setTime(d.getTime() + 7 * 24 * 60 * 60 * 1000); // 7 giorni in millisecondi
        const expires = "expires=" + d.toUTCString();
        document.cookie =
          "auth_token=" +
          result.token +
          ";expires=" +
          expires +
          "; path=/; secure; samesite=Strict";

        alert(result.message); // Opzionale: mostra un messaggio di successo
        window.location.href = "/home"; // Reindirizza alla home
      } else {
        const error = await response.json();
        alert(error.error || "Errore durante il login.");
      }
    } catch (error) {
      console.error("Errore nella richiesta:", error);
      alert("Errore di connessione. Riprova più tardi.");
    }
  });
