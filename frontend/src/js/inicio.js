
const API_URL =
    "../../backend/api/routes/routes.php?recurso=productos&vista=catalogo";

const contenedorCategorias =
    document.getElementById("contenedorCategorias");

const contenedorDestacados =
    document.getElementById("contenedorDestacados");

const mensajeCategorias =
    document.getElementById("mensajeCategorias");

const mensajeProductos =
    document.getElementById("mensajeProductos");

// Imágenes locales de los productos
const imagenesProductos = {
    1: "../img/laptop-asus.jpg",
    2: "../img/memoria-ram.jpg",
    3: "../img/ssd-1tb.jpg",
    4: "../img/teclado.jpg",
    5: "../img/mouse.jpg",
    7: "../img/monitor.jpg"
};

// Cargar información desde la API
async function cargarInicio() {

    try {

        const respuesta = await fetch(API_URL);
        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error("No se pudieron cargar los productos.");
        }

        const productos = resultado.data || [];

        mostrarCategorias(productos);
        mostrarDestacados(productos);

    } catch (error) {

        mensajeCategorias.textContent =
            "No se pudieron cargar las categorías.";

        mensajeProductos.textContent =
            "No se pudieron cargar los productos.";

        console.error(error);
    }
}

// Mostrar categorías
function mostrarCategorias(productos) {

    contenedorCategorias.innerHTML = "";

    const categorias = [
        ...new Set(
            productos
                .map(producto => producto.Categoria)
                .filter(Boolean)
        )
    ].sort();

    mensajeCategorias.textContent =
        categorias.length + " categorías disponibles";

    if (categorias.length === 0) {
        contenedorCategorias.innerHTML =
            '<p class="text-slate-500">No hay categorías disponibles.</p>';
        return;
    }

    categorias.forEach(categoria => {

        const tarjeta = document.createElement("article");

        tarjeta.className =
            "bg-white rounded-xl shadow-sm border border-slate-200 p-7 hover:shadow-lg hover:border-cyan-400 transition";

        const titulo = document.createElement("h3");

        titulo.className =
            "text-xl font-bold text-slate-900";

        titulo.textContent = categoria;

        const descripcion = document.createElement("p");

        descripcion.className =
            "text-slate-500 mt-3 text-sm";

        descripcion.textContent =
            "Explora nuestros productos de " + categoria + ".";

        const enlace = document.createElement("a");

        enlace.href = "./catalogo.html#productos";

        enlace.textContent = "Ver productos →";

        enlace.className =
            "inline-block mt-5 font-semibold text-cyan-600 hover:text-cyan-800";

        tarjeta.appendChild(titulo);
        tarjeta.appendChild(descripcion);
        tarjeta.appendChild(enlace);

        contenedorCategorias.appendChild(tarjeta);
    });
}

// Mostrar cuatro productos destacados
function mostrarDestacados(productos) {

    contenedorDestacados.innerHTML = "";

    const disponibles = productos.filter(
        producto => Number(producto.Stock) > 0
    );

    const destacados = disponibles.slice(0, 4);

    mensajeProductos.textContent =
        destacados.length + " productos destacados";

    if (destacados.length === 0) {

        contenedorDestacados.innerHTML =
            '<p class="text-slate-500">No hay productos disponibles.</p>';

        return;
    }

    destacados.forEach(producto => {

        const tarjeta = document.createElement("article");

        tarjeta.className =
            "bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg transition";

        const imagen = document.createElement("img");

        const id = Number(producto.IdProducto);

        imagen.src = imagenesProductos[id] || "";
        imagen.alt = producto.Nombre;
        imagen.loading = "lazy";

        imagen.className =
            "w-full h-48 object-contain p-5 bg-white";

        imagen.onerror = function () {
            this.alt = "Imagen no disponible";
        };

        const contenido = document.createElement("div");

        contenido.className = "p-5";

        const categoria = document.createElement("p");

        categoria.className =
            "text-xs font-bold uppercase text-cyan-600";

        categoria.textContent =
            producto.Categoria || "General";

        const nombre = document.createElement("h3");

        nombre.className =
            "text-lg font-bold mt-2";

        nombre.textContent = producto.Nombre;

        const precio = document.createElement("p");

        precio.className =
            "text-xl font-extrabold mt-4";

        precio.textContent =
            "S/ " + Number(producto.Precio).toFixed(2);

        const enlace = document.createElement("a");

        enlace.href = "./catalogo.html#productos";

        enlace.textContent = "Ver catálogo";

        enlace.className =
            "block text-center mt-5 bg-cyan-500 hover:bg-cyan-600 text-white font-semibold py-3 rounded-lg";

        contenido.appendChild(categoria);
        contenido.appendChild(nombre);
        contenido.appendChild(precio);
        contenido.appendChild(enlace);

        tarjeta.appendChild(imagen);
        tarjeta.appendChild(contenido);

        contenedorDestacados.appendChild(tarjeta);
    });
}

// Iniciar página principal
cargarInicio();
