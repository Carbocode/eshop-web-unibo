import "./style.scss";

document
  .getElementById("register-form")
  .addEventListener("submit", async (event) => {
    event.preventDefault(); // Previene il comportamento predefinito del modulo

    // Recupera i dati dal modulo
    const formData = {
      full_name: document.getElementById("fullName").value.trim(),
      email: document.getElementById("email").value.trim(),
      password: document.getElementById("password").value,
      phone: document.getElementById("phone").value.trim(),
      address: document.getElementById("address").value.trim(),
      city: document.getElementById("city").value.trim(),
      province: document.getElementById("province").value.trim(),
      zip: document.getElementById("zip").value.trim(),
      country: document.getElementById("country").value.trim(),
    };

    // Esegui controlli sui campi obbligatori
    if (
      !formData.full_name ||
      !formData.email ||
      !formData.password ||
      !formData.phone
    ) {
      alert(
        "Per favore, compila tutti i campi obbligatori: Nome Completo, Email, Password e Telefono."
      );
      return;
    }

    try {
      // Effettua la chiamata POST alla tua API
      const response = await fetch(
        "https://localhost:8000/src/accounts/create.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formData),
        }
      );

      // Gestione della risposta
      if (response.ok) {
        const data = await response.json();
        alert(data.message || "Registrazione completata con successo!");
        document.getElementById("register-form").reset(); // Resetta il modulo
      } else {
        const errorData = await response.json();
        alert(
          errorData.error ||
            "Si è verificato un errore durante la registrazione."
        );
      }
    } catch (error) {
      console.error("Errore durante la chiamata API:", error);
      alert("Si è verificato un errore. Riprova più tardi.");
    }
  });
