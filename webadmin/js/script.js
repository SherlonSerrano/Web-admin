document.getElementById("menu-home").addEventListener("click", () => {
  showHome();
});

document.getElementById("menu-product").addEventListener("click", () => {
  showProductPage();
}); /* Cargar la página de productos al cargar la página web */

// Evento para cerrar sesión.
// Al hacer clic, pide confirmación; si acepta, envía DELETE a api/login.php,
// el backend destruye la sesión y se redirige al login.
document.getElementById("btnLogout").addEventListener("click", async () => {
  const confirmar = confirm("¿Estás seguro de que deseas cerrar sesión?");
  if (!confirmar) {
    return;
  }

  try {
    // Llama al endpoint de logout definido en login.php
    const response = await fetch("api/login.php", {
      method: "DELETE",
    });

    const data = await response.json();

    // Tu logout devuelve: { status: "success", message: "..." }
    if (data.status === "success") {
      // Redirige al login después de cerrar la sesión
      window.location.href = "login.html";
    } else {
      alert(data.message || "No se pudo cerrar la sesión");
    }
  } catch (error) {
    console.log("JS: Error al cerrar sesión", error);
  }
});

showProductPage(); /* Cargar la página de productos al cargar la página web */

// Carga la vista de inicio y reemplaza el contenido del contenedor principal.
function showHome() {
  let rutaArchivo = "views/home.html";

  fetch(rutaArchivo)
    .then((Response) => Response.text())
    .then((data) => {
      document.getElementById("div_content").innerHTML = data;
    });
}

// Carga la vista de productos dentro del contenedor principal.
// Inserta primero el HTML y después llama a iniciarProductos(),
// para conectar los eventos y cargar los datos cuando los elementos ya existen.
async function showProductPage() {
  const contenedor = document.getElementById("div_content");

  try {
    const respuesta = await fetch("views/view_product.html");

    // Comprobar que el archivo de la vista se cargó correctamente.
    if (!respuesta.ok) {
      throw new Error(
        `No se pudo cargar view_product.html: HTTP ${respuesta.status}`,
      );
    }

    const html = await respuesta.text();

    // Primero crear los elementos de la vista.
    contenedor.innerHTML = html;

    // Después conectar eventos y cargar los productos.
    // No usamos typeof para no ocultar un error si falta esta función.
    await iniciarProductos();
  } catch (error) {
    console.error("Error al abrir la vista de productos:", error);
    alert("No se pudo abrir la vista de productos. Revisa la consola.");
  }
}

function limpiarScript(id) {
  /* Función para eliminar un script del DOM */
  let script = document.getElementById(id);
  if (script) {
    script.remove();
  }
}
