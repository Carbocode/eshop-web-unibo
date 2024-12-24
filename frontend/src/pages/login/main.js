import './style.css';

document
        .getElementById("login-form")
        .addEventListener("submit", async (e) => {
          e.preventDefault();
          const errorDiv = document.getElementById("error-message");

          try {
            const response = await fetch("/login", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                email: document.getElementById("email").value,
                password: document.getElementById("password").value,
              }),
            });

            const data = await response.json();

            if (response.ok) {
              localStorage.setItem("token", data.token);
              window.location.href = "/dashboard";
            } else {
              errorDiv.textContent = data.error;
              errorDiv.classList.remove("hidden");
            }
          } catch (err) {
            errorDiv.textContent = "Login failed. Please try again.";
            errorDiv.classList.remove("hidden");
          }
        });