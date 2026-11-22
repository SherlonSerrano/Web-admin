document
  .getElementById("formLogin")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    try {
      email = document.getElementById("correo").value;
      password = document.getElementById("password").value;

      const response = await fetch(`api/login.php`, {
        method: "POST",
        body: JSON.stringify({ email, password })
      });

      const data = await response.json();

      if(data.status) {
        window.location.href = "index.html"
      } else {
        alert(data.mensaje)
      }

    } catch (error) {
        console.log("JS: Error en login")
    }
  });
