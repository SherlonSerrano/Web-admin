// Dirección base de la API de productos y categorías.
// Las rutas de cada petición se construyen a partir de esta dirección.
var api = "http://localhost/api";

// Obtiene la lista de productos del endpoint general y la muestra en la tabla.
// No utiliza la respuesta paginada de filtrarProductos().
// La inicialización actual utiliza filtrarProductos(1), no esta función.
async function getListProduct() {
  let rutaServicio = `${api}/products`; //Carga api/products/index.php

  const respuesta = await fetch(rutaServicio);
  const productos = await respuesta.json();

  renderProducts(productos); //Llamar a la función para renderizar los productos en la tabla
}

// Reemplaza las filas de la tabla con los productos recibidos.
// Genera los botones de detalle, edición y eliminación para cada producto.
// Esta función solo muestra datos: no realiza peticiones a la API.
function renderProducts(productos) {
  const tabla = document.getElementById("table-products");

  tabla.innerHTML = " "; // Limpiar la tabla antes de llenarla

  productos.forEach((item) => {
    // Recorrer cada producto y crear una fila en la tabla
    const fila = document.createElement("tr"); //Crear una nueva fila para cada producto

    fila.innerHTML = `
            <td class="border px-4 py-2 border-y">${item.id}</td>
            <td class="border px-4 py-2 border-y">${item.title}</td>
            <td class="border px-4 py-2 border-y">${item.category}</td>
            <td class="border px-4 py-2 border-y">${item.price}</td>
            <td class="px-4 py-2 border-y">
                <button class="bg-yellow-500 text-white px-2 py-1 rounded" 
                onclick="verDetalle(${item.id})"><i class="fa-solid fa-circle-info"></i></button>
                <button class="bg-green-500 text-white px-2 py-1 rounded" 
                onclick="abrirModalEditar(${item.id})"><i class="fa-regular fa-pen-to-square"></i></button>
                <button class="bg-red-500 text-white px-2 py-1 rounded" 
                onclick="abrirModalEliminar(${item.id})"><i class="fa-solid fa-trash-can"></i></button>
            </td>
        `;

    tabla.appendChild(fila);
  });
}

// Consulta las categorías disponibles y comprueba que la respuesta sea una lista.
// Devuelve las categorías para que renderFilters() construya sus botones.
// Lanza un error si la respuesta HTTP falla o el formato no es el esperado.
async function getListCategories() {
  const respuesta = await fetch(`${api}/categories/index.php`);

  if (!respuesta.ok) {
    throw new Error(`Error al cargar categorías: HTTP ${respuesta.status}`);
  }

  const categorias = await respuesta.json();

  if (!Array.isArray(categorias)) {
    throw new Error("La API no devolvió una lista de categorías");
  }

  return categorias;
}

// Consulta las categorías y reconstruye sus botones de filtro.
// Incluye la opción TODAS, que permite consultar sin restringir la categoría.
// Cada botón ejecuta FilterCategory() con la categoría correspondiente.
async function renderFilters() {
  try {
    const categorias = await getListCategories();
    const contenedor = document.getElementById("div_filtros");

    contenedor.innerHTML = "";

    const crearBoton = (texto, categoriaId = null) => {
      const btn = document.createElement("button");

      btn.type = "button";
      btn.textContent = texto;
      btn.className = "btn-filtro bg-blue-500 text-white px-3 py-2 rounded";

      btn.addEventListener("click", () => {
        FilterCategory(categoriaId, btn);
      });

      contenedor.appendChild(btn);
    };

    crearBoton("TODAS");

    categorias.forEach((categoria) => {
      crearBoton(categoria.title.toUpperCase(), categoria.id);
    });
  } catch (error) {
    console.error("Error al mostrar categorías:", error);
    alert("No se pudieron cargar las categorías. Revisa la consola.");
  }
}

// Consulta los productos de una categoría; null permite consultar todas.
// Actualiza la tabla y resalta el botón seleccionado.
// Como esta consulta no está paginada, limpia los números de página
// y desactiva los botones Anterior y Siguiente.
async function FilterCategory(categoriaId, botonSeleccionado) {
  try {
    let url = `${api}/products/index.php`;

    if (categoriaId !== null) {
      url += `?cat=${encodeURIComponent(categoriaId)}`;
    }

    const respuesta = await fetch(url);

    if (!respuesta.ok) {
      throw new Error(`HTTP ${respuesta.status}`);
    }

    const productos = await respuesta.json();

    if (!Array.isArray(productos)) {
      throw new Error("La API no devolvió una lista de productos");
    }

    renderProducts(productos);

    // Esta ruta devuelve una lista sin paginación.
    document.getElementById("numerosPagina").innerHTML = "";
    document.getElementById("btnAnterior").disabled = true;
    document.getElementById("btnSiguiente").disabled = true;

    document.querySelectorAll(".btn-filtro").forEach((btn) => {
      btn.classList.remove("bg-green-600");
      btn.classList.add("bg-blue-500");
    });

    botonSeleccionado.classList.remove("bg-blue-500");
    botonSeleccionado.classList.add("bg-green-600");
  } catch (error) {
    console.error("Error al filtrar por categoría:", error);
    alert("No se pudieron cargar los productos de esa categoría.");
  }
}

// Estado de las imágenes del producto mostrado en el modal de detalles.
// Guarda la lista de imágenes y la posición de la imagen visible.
let imagenesProducto = [];
let indiceImagen = 0;

// Consulta un producto por su id y muestra sus datos en el modal de detalles.
// Reinicia el índice de imágenes para mostrar primero la imagen inicial.
async function verDetalle(id) {
  const producto = await cargarProducto(id);

  if (!producto) {
    alert("No se pudo cargar el producto");
    return;
  }

  imagenesProducto = producto.images || [];
  indiceImagen = 0;

  document.getElementById("txtTitulo").textContent = producto.title;
  document.getElementById("txtDescripcion").textContent = producto.description;
  document.getElementById("txtPrecio").textContent = producto.price;

  mostrarImagen();

  document.getElementById("modal").classList.remove("hidden");
  document.getElementById("modal").classList.add("flex");
}

// Oculta el modal de detalles.
// No cierra el modal de edición, que utiliza cerrarModales().
function cerrarModal() {
  //Ocultar el modal al hacer clic en el botón "Cerrar"
  document.getElementById("modal").classList.add("hidden");
  document.getElementById("modal").classList.remove("flex");
}

// Actualiza la imagen del modal usando el índice actual.
// Si el producto no tiene imágenes, utiliza la imagen de respaldo.
function mostrarImagen() {
  const img = document.getElementById("imgProducto");
  img.src =
    imagenesProducto.length > 0
      ? imagenesProducto[indiceImagen].url
      : "img/producto.jpg";
}

// Cambia la imagen del producto: 1 avanza y -1 retrocede.
// El recorrido es circular: después de la última imagen vuelve a la primera.
// Si no hay imágenes, no realiza ningún cambio.
function cambiarImagen(direccion) {
  if (imagenesProducto.length === 0) return;
  indiceImagen =
    (indiceImagen + direccion + imagenesProducto.length) %
    imagenesProducto.length;

  mostrarImagen();
}

// Consulta y devuelve un solo producto por su id; no modifica la tabla.
// Se utiliza para obtener los datos del modal de detalles.
// Devuelve null si ocurre un error durante la consulta o al interpretar el JSON.
async function cargarProducto(id) {
  try {
    const respuesta = await fetch(`${api}/products/index.php?id=${id}`);
    const producto = await respuesta.json();
    return producto;
  } catch (error) {
    console.error("Error al cargar el producto: ", error);
    return null;
  }
}

// Envía los filtros del formulario junto con la página y su tamaño.
// Muestra los productos recibidos y reconstruye los botones de paginación.
// También se utiliza para cargar la primera página al abrir la vista.
async function filtrarProductos(pagina = 1) {
  try {
    const formdata = new FormData(document.getElementById("formFiltro"));

    const selector = document.getElementById("por_pagina");
    const porPagina = Number(selector?.value || 10);

    formdata.set("por_pagina", porPagina);
    formdata.set("pagina", pagina);

    const respuesta = await fetch(`${api}/products/producto.php`, {
      method: "POST",
      body: formdata,
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !Array.isArray(resultado.data)) {
      throw new Error(
        resultado.mensaje ||
          resultado.error ||
          "Respuesta no válida del servidor",
      );
    }

    renderProducts(resultado.data);

    const totalPaginas = Math.ceil(resultado.total / porPagina);

    renderPages(totalPaginas, pagina);
  } catch (error) {
    console.error("Error al filtrar productos:", error);
    alert(error.message);
  }
}

// Crea los botones de paginación y resalta la página actual.
// Cada botón solicita los productos de su página mediante filtrarProductos().
function renderPages(totalPaginas, pagina) {
  const numerosPagina = document.getElementById("numerosPagina");
  numerosPagina.innerHTML = "";

  for (let i = 1; i <= totalPaginas; i++) {
    const boton = document.createElement("button");
    boton.type = "button";
    boton.textContent = i;
    boton.dataset.pagina = i;

    if (i == pagina) {
      boton.classList.add("bg-blue-600", "text-white");
    } else {
      boton.classList.add("border");
    }

    boton.addEventListener("click", () => {
      filtrarProductos(i);
    });

    numerosPagina.appendChild(boton);
  }
}

// Consulta un producto por su id y completa los campos del formulario de edición.
// Después muestra el modal para que el usuario pueda modificar sus datos.
async function abrirModalEditar(id) {
  const respuesta = await fetch(`${api}/products/index.php?id=${id}`);
  const producto = await respuesta.json();

  document.getElementById("edit_id").value = producto.id;
  document.getElementById("edit_nombre").value = producto.title;
  document.getElementById("edit_precio").value = producto.price;
  document.getElementById("edit_stock").value = producto.stock;
  document.getElementById("edit_state").value = producto.state;

  document.getElementById("modalEditar").classList.remove("hidden");
  document.getElementById("modalEditar").classList.add("flex");
}

// Oculta el modal de edición.
// Aunque el nombre está en plural, actualmente solo cierra modalEditar.
function cerrarModales() {
  document.getElementById("modalEditar").classList.add("hidden");
  document.getElementById("modalEditar").classList.remove("flex");
}

// Prepara la vista de productos después de insertar su HTML en div_content.
// Conecta los eventos de los formularios de filtro y edición de la vista actual.
// Carga la primera página de productos y los filtros de categorías.
// Debe ejecutarse una vez por cada nueva inserción de view_product.html.
async function iniciarProductos() {
  const formFiltro = document.getElementById("formFiltro");
  const formEditar = document.getElementById("formEditar");
  const tabla = document.getElementById("table-products");

  // Si falta un elemento principal, mostramos un error claro.
  if (!formFiltro || !tabla) {
    console.error(
      "No se puede iniciar Productos: falta #formFiltro o #table-products.",
    );
    return;
  }

  // Registrar el filtro cuando su formulario ya existe.
  // preventDefault evita que el navegador recargue index.html?nombre=...
  formFiltro.addEventListener("submit", function (e) {
    e.preventDefault();
    filtrarProductos(1);
  });

  // Registrar la edición cuando el formulario ya existe.
  if (formEditar) {
    formEditar.addEventListener("submit", async function (e) {
      e.preventDefault();

      // Recoger los campos que utiliza actualizarProducto() en el modelo.
      // El stock y el estado deben incluirse explícitamente en el JSON enviado.
      const producto = {
        id: document.getElementById("edit_id").value,
        nombre: document.getElementById("edit_nombre").value,
        precio: document.getElementById("edit_precio").value,

        // Cantidad disponible indicada en el formulario.
        stock: document.getElementById("edit_stock").value,

        // Valor seleccionado: "1" para Activo y "0" para Inactivo.
        state: document.getElementById("edit_state").value,
      };

      try {
        // Enviar los datos de edición como JSON al endpoint de actualización.
        const respuesta = await fetch(`${api}/products/producto.php`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(producto),
        });

        const resultado = await respuesta.json();

        if (resultado.status == "success") {
          alert(resultado.mensaje);

          // Corrección: cerrar el modal de EDICIÓN.
          // cerrarModal() corresponde al modal de detalles.
          cerrarModales();

          // Actualizar la tabla después de editar.
          await filtrarProductos(1);
        } else {
          alert(resultado.mensaje);
        }
      } catch (error) {
        alert("Error de conexión");
        console.error("Error al editar el producto:", error);
      }
    });
  } else {
    console.warn("No se encontró #formEditar en la vista de productos.");
  }

  // Cargar productos una sola vez, usando tu filtro y paginación.
  await filtrarProductos(1);

  // Cargar los botones de categorías si existe su contenedor.
  if (document.getElementById("div_filtros")) {
    await renderFilters();
  } else {
    console.warn("No se encontró #div_filtros; no se cargaron las categorías.");
  }
}

// Solicita confirmación antes de borrar definitivamente un producto.
// Utiliza la confirmación del navegador; no requiere un modal HTML.
async function abrirModalEliminar(id) {
  // Mostrar claramente el alcance del borrado antes de enviar la petición.
  const confirmado = window.confirm(
    `¿Deseas eliminar definitivamente el producto con ID ${id}?\n\n` +
      "También se eliminarán sus registros de imágenes de la base de datos.\n" +
      "Los archivos físicos de imagen se conservarán en la carpeta.\n\n" +
      "Esta acción no se puede deshacer desde la página.",
  );

  // Si el usuario cancela, no se realiza ninguna petición.
  if (!confirmado) {
    return;
  }

  try {
    // El controlador espera la propiedad "id_producto" en el JSON.
    const respuesta = await fetch(`${api}/products/producto.php`, {
      method: "DELETE",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id_producto: id,
      }),
    });

    const resultado = await respuesta.json();

    // El controlador de eliminación devuelve status como booleano.
    if (!respuesta.ok || resultado.status !== true) {
      throw new Error(resultado.mensaje || "No se pudo eliminar el producto.");
    }

    alert(resultado.mensaje || "Producto eliminado correctamente.");
  } catch (error) {
    console.error("Error al eliminar el producto:", error);
    alert(error.message || "Ocurrió un error al eliminar el producto.");
    return;
  }

  // Actualizar la tabla conservando los filtros actuales.
  // Volver a la primera página evita quedar en una página que ya no existe.
  await filtrarProductos(1);
}
