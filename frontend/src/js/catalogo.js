
const API_URL =
    "../../backend/api/routes/routes.php?recurso=productos&vista=catalogo";

// ELEMENTOS HTML

const contenedorProductos = document.getElementById("contenedorProductos");
const mensajeCatalogo = document.getElementById("mensajeCatalogo");
const cantidadProductos = document.getElementById("cantidadProductos");
const buscarProducto = document.getElementById("buscarProducto");
const filtroCategoria = document.getElementById("filtroCategoria");

const modalProducto = document.getElementById("modalProducto");

const tablaCarrito = document.getElementById("tablaCarrito");
const totalCarrito = document.getElementById("totalCarrito");
const contadorCarrito = document.getElementById("contadorCarrito");

let productos = [];
let productoSeleccionado = null;
let carrito = cargarCarrito();

// IMÁGENES LOCALES

const imagenesProductos = {
    1: "../img/laptop-asus.jpg",
    2: "../img/memoria-ram.jpg",
    3: "../img/ssd-1tb.jpg",
    4: "../img/teclado.jpg",
    5: "../img/mouse.jpg",
    7: "../img/monitor.jpg"
};

// =====================================
// MENSAJES
// =====================================

function mostrarMensaje(texto, tipo = "error") {
    mensajeCatalogo.textContent = texto;

    mensajeCatalogo.className = tipo === "error"
        ? "mb-6 text-sm font-semibold text-red-600"
        : "mb-6 text-sm font-semibold text-green-600";
}

// =====================================
// CARGAR CARRITO GUARDADO
// =====================================

function cargarCarrito() {
    try {
        const guardado = JSON.parse(
            localStorage.getItem("techstore_carrito")
        );

        return Array.isArray(guardado) ? guardado : [];
    } catch (error) {
        return [];
    }
}

// =====================================
// GUARDAR CARRITO
// =====================================

function guardarCarrito() {
    localStorage.setItem(
        "techstore_carrito",
        JSON.stringify(carrito)
    );
}

// =====================================
// CARGAR PRODUCTOS
// =====================================

async function cargarProductos() {

    contenedorProductos.innerHTML = `
        <div class="col-span-full text-center py-12 text-slate-500">
            Cargando productos...
        </div>
    `;

    try {

        const respuesta = await fetch(API_URL);
        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(
                resultado.message || "No se pudieron cargar los productos."
            );
        }

        productos = resultado.data || [];

        cargarCategorias();
        mostrarProductos(productos);
        sincronizarCarrito();

    } catch (error) {

        contenedorProductos.innerHTML = `
            <div class="col-span-full bg-white rounded-xl p-8 text-center">
                <p class="text-red-600 font-semibold">
                    No se pudieron cargar los productos.
                </p>
                <p class="text-slate-500 mt-2">
                    Intenta actualizar la página.
                </p>
            </div>
        `;

        cantidadProductos.textContent = "Sin resultados";
        mostrarMensaje(error.message);

        console.error(error);
    }
}

// =====================================
// CARGAR CATEGORÍAS
// =====================================

function cargarCategorias() {

    filtroCategoria.innerHTML = "";

    const opcionTodas = document.createElement("option");
    opcionTodas.value = "";
    opcionTodas.textContent = "Todas las categorías";

    filtroCategoria.appendChild(opcionTodas);

    const categorias = [
        ...new Set(
            productos
                .map(producto => producto.Categoria)
                .filter(Boolean)
        )
    ].sort();

    categorias.forEach(categoria => {

        const opcion = document.createElement("option");

        opcion.value = categoria;
        opcion.textContent = categoria;

        filtroCategoria.appendChild(opcion);
    });
}

// =====================================
// MOSTRAR PRODUCTOS
// =====================================

function mostrarProductos(lista) {

    contenedorProductos.innerHTML = "";

    cantidadProductos.textContent =
        `${lista.length} producto${lista.length !== 1 ? "s" : ""}`;

    if (lista.length === 0) {

        const mensaje = document.createElement("div");

        mensaje.className =
            "col-span-full bg-white rounded-xl p-10 text-center text-slate-500";

        mensaje.textContent =
            "No se encontraron productos disponibles.";

        contenedorProductos.appendChild(mensaje);
        return;
    }

    lista.forEach(producto => {

        const tarjeta = document.createElement("article");

        tarjeta.className =
            "bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg transition duration-300 flex flex-col h-full";

        // IMAGEN

        const imagen = document.createElement("div");

        imagen.className =
            "h-56 w-full bg-white flex items-center justify-center p-5 overflow-hidden shrink-0";

        const foto = document.createElement("img");

        const id = Number(producto.IdProducto);

        foto.src = imagenesProductos[id] || "";
        foto.alt = producto.Nombre;

        foto.className =
            "w-full h-full object-contain transition duration-300 hover:scale-105";

        foto.loading = "lazy";

        foto.onerror = function () {
            this.style.display = "none";

            const alternativa = document.createElement("span");
            alternativa.textContent = "Imagen no disponible";
            alternativa.className = "text-slate-400 text-sm";

            imagen.appendChild(alternativa);
        };

        imagen.appendChild(foto);

        // CONTENIDO

        const contenido = document.createElement("div");
        contenido.className = "p-5 flex flex-col flex-1";

        const categoria = document.createElement("p");

        categoria.className =
            "text-xs uppercase tracking-wider font-bold text-cyan-600";

        categoria.textContent = producto.Categoria || "General";

        const nombre = document.createElement("h3");

        nombre.className =
            "text-lg font-bold mt-2 text-slate-900";

        nombre.textContent = producto.Nombre;

        const descripcion = document.createElement("p");

        descripcion.className =
            "text-sm text-slate-500 mt-2 min-h-10";

        descripcion.textContent =
            producto.Descripcion || "Producto tecnológico de TechStore.";

        const precio = document.createElement("p");

        precio.className =
            "text-2xl font-extrabold text-slate-950 mt-5";

        precio.textContent =
            `S/ ${Number(producto.Precio).toFixed(2)}`;

        const stock = document.createElement("p");

        stock.className = "text-sm mt-3";

        const cantidadStock = Number(producto.Stock);

        if (cantidadStock > 0) {
            stock.textContent = `Stock disponible: ${cantidadStock}`;
            stock.classList.add("text-green-600");
        } else {
            stock.textContent = "Agotado";
            stock.classList.add("text-red-600");
        }

        // BOTÓN CONSULTAR

        const boton = document.createElement("button");

        boton.type = "button";

        boton.textContent = cantidadStock > 0
            ? "Consultar producto"
            : "Sin disponibilidad";

        boton.className =
            "w-full mt-auto bg-cyan-500 hover:bg-cyan-600 text-white font-semibold py-3 rounded-lg transition";

        boton.disabled = cantidadStock <= 0;

        if (cantidadStock <= 0) {
            boton.className =
                "w-full mt-auto bg-slate-300 text-slate-500 font-semibold py-3 rounded-lg cursor-not-allowed";
        } else {
            boton.addEventListener("click", () => {
                abrirDetalleProducto(producto);
            });
        }

        contenido.appendChild(categoria);
        contenido.appendChild(nombre);
        contenido.appendChild(descripcion);
        contenido.appendChild(precio);
        contenido.appendChild(stock);
        contenido.appendChild(boton);

        tarjeta.appendChild(imagen);
        tarjeta.appendChild(contenido);

        contenedorProductos.appendChild(tarjeta);
    });
}

// =====================================
// ABRIR DETALLES
// =====================================

function abrirDetalleProducto(producto) {

    productoSeleccionado = producto;

    const imagen = document.getElementById("modalImagen");

    imagen.src =
        imagenesProductos[Number(producto.IdProducto)] || "";

    imagen.alt = producto.Nombre;
    imagen.style.display = "block";

    imagen.onerror = function () {
        this.style.display = "none";
    };

    document.getElementById("modalCategoria").textContent =
        producto.Categoria || "General";

    document.getElementById("modalNombre").textContent =
        producto.Nombre;

    document.getElementById("modalDescripcion").textContent =
        producto.Descripcion || "Sin descripción disponible.";

    document.getElementById("modalPrecio").textContent =
        `S/ ${Number(producto.Precio).toFixed(2)}`;

    const stock = document.getElementById("modalStock");
    const cantidad = Number(producto.Stock);

    stock.textContent = cantidad > 0
        ? `Stock disponible: ${cantidad}`
        : "Producto agotado";

    stock.className = cantidad > 0
        ? "mt-3 font-semibold text-green-600"
        : "mt-3 font-semibold text-red-600";

    const botonAgregar = document.getElementById("btnAgregarCarrito");

    botonAgregar.disabled = cantidad <= 0;

    botonAgregar.textContent = cantidad > 0
        ? "Agregar al carrito"
        : "Producto agotado";

    botonAgregar.className = cantidad > 0
        ? "mt-7 w-full bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-3 rounded-lg transition"
        : "mt-7 w-full bg-slate-300 text-slate-500 font-bold py-3 rounded-lg cursor-not-allowed";

    modalProducto.classList.remove("hidden");
    modalProducto.classList.add("flex");

    document.body.style.overflow = "hidden";
}

// =====================================
// CERRAR DETALLES
// =====================================

function cerrarDetalleProducto() {

    modalProducto.classList.add("hidden");
    modalProducto.classList.remove("flex");

    document.body.style.overflow = "";

    productoSeleccionado = null;
}

document.getElementById("btnCerrarModal")
    .addEventListener("click", cerrarDetalleProducto);

document.getElementById("btnCerrarDetalle")
    ?.addEventListener("click", cerrarDetalleProducto);

modalProducto.addEventListener("click", evento => {

    if (evento.target === modalProducto) {
        cerrarDetalleProducto();
    }
});

document.addEventListener("keydown", evento => {

    if (evento.key === "Escape") {
        cerrarDetalleProducto();
    }
});

// =====================================
// AGREGAR AL CARRITO
// =====================================

document.getElementById("btnAgregarCarrito")
    .addEventListener("click", () => {

        if (!productoSeleccionado) return;

        const id = Number(productoSeleccionado.IdProducto);
        const stockDisponible = Number(productoSeleccionado.Stock);

        const existente = carrito.find(
            item => Number(item.IdProducto) === id
        );

        if (existente) {

            if (existente.cantidad >= stockDisponible) {
                mostrarMensaje(
                    "No puedes agregar más unidades de las disponibles.",
                    "error"
                );
                return;
            }

            existente.cantidad++;

        } else {

            carrito.push({
                IdProducto: id,
                Nombre: productoSeleccionado.Nombre,
                Precio: Number(productoSeleccionado.Precio),
                Stock: stockDisponible,
                cantidad: 1
            });

        }

        guardarCarrito();
        mostrarCarrito();
        cerrarDetalleProducto();

        mostrarMensaje(
            `${productoSeleccionado?.Nombre || "Producto"} agregado al carrito.`,
            "success"
        );

        document.getElementById("carrito").scrollIntoView({
            behavior: "smooth"
        });
    });

// =====================================
// SINCRONIZAR CARRITO CON STOCK ACTUAL
// =====================================

function sincronizarCarrito() {

    carrito = carrito
        .map(item => {

            const productoActual = productos.find(
                producto =>
                    Number(producto.IdProducto) === Number(item.IdProducto)
            );

            if (!productoActual) return null;

            const stock = Number(productoActual.Stock);

            if (stock <= 0) return null;

            return {
                IdProducto: Number(productoActual.IdProducto),
                Nombre: productoActual.Nombre,
                Precio: Number(productoActual.Precio),
                Stock: stock,
                cantidad: Math.min(
                    Math.max(1, Number(item.cantidad) || 1),
                    stock
                )
            };
        })
        .filter(Boolean);

    guardarCarrito();
    mostrarCarrito();
}

// =====================================
// MOSTRAR CARRITO
// =====================================

function mostrarCarrito() {

    tablaCarrito.innerHTML = "";

    let total = 0;
    let cantidadTotal = 0;

    if (carrito.length === 0) {

        tablaCarrito.innerHTML = `
            <tr>
                <td colspan="5"
                    class="text-center py-10 text-slate-500">
                    Tu carrito está vacío.
                </td>
            </tr>
        `;

    } else {

        carrito.forEach(item => {

            const subtotal = item.Precio * item.cantidad;

            total += subtotal;
            cantidadTotal += item.cantidad;

            const fila = document.createElement("tr");

            fila.className = "border-t border-slate-200";

            // PRODUCTO

            const celdaNombre = document.createElement("td");

            celdaNombre.className = "px-5 py-4 font-semibold";
            celdaNombre.textContent = item.Nombre;

            // PRECIO

            const celdaPrecio = document.createElement("td");

            celdaPrecio.className = "px-5 py-4";
            celdaPrecio.textContent =
                `S/ ${item.Precio.toFixed(2)}`;

            // CANTIDAD

            const celdaCantidad = document.createElement("td");

            celdaCantidad.className = "px-5 py-4";

            const controles = document.createElement("div");

            controles.className = "flex items-center gap-3";

            const botonMenos = document.createElement("button");

            botonMenos.type = "button";
            botonMenos.textContent = "-";
            botonMenos.className =
                "bg-slate-200 hover:bg-slate-300 rounded px-3 py-1 font-bold";

            botonMenos.addEventListener("click", () => {
                cambiarCantidad(item.IdProducto, -1);
            });

            const cantidad = document.createElement("span");

            cantidad.textContent = item.cantidad;
            cantidad.className = "font-semibold";

            const botonMas = document.createElement("button");

            botonMas.type = "button";
            botonMas.textContent = "+";
            botonMas.className =
                "bg-slate-200 hover:bg-slate-300 rounded px-3 py-1 font-bold";

            botonMas.disabled = item.cantidad >= item.Stock;

            botonMas.addEventListener("click", () => {
                cambiarCantidad(item.IdProducto, 1);
            });

            if (botonMas.disabled) {
                botonMas.classList.add("opacity-40", "cursor-not-allowed");
            }

            controles.appendChild(botonMenos);
            controles.appendChild(cantidad);
            controles.appendChild(botonMas);

            celdaCantidad.appendChild(controles);

            // SUBTOTAL

            const celdaSubtotal = document.createElement("td");

            celdaSubtotal.className = "px-5 py-4 font-bold";

            celdaSubtotal.textContent =
                `S/ ${subtotal.toFixed(2)}`;

            // ELIMINAR

            const celdaAccion = document.createElement("td");

            celdaAccion.className = "px-5 py-4 text-center";

            const botonEliminar = document.createElement("button");

            botonEliminar.type = "button";
            botonEliminar.textContent = "Eliminar";

            botonEliminar.className =
                "text-red-600 hover:text-red-800 font-semibold";

            botonEliminar.addEventListener("click", () => {
                eliminarDelCarrito(item.IdProducto);
            });

            celdaAccion.appendChild(botonEliminar);

            fila.appendChild(celdaNombre);
            fila.appendChild(celdaPrecio);
            fila.appendChild(celdaCantidad);
            fila.appendChild(celdaSubtotal);
            fila.appendChild(celdaAccion);

            tablaCarrito.appendChild(fila);
        });
    }

    totalCarrito.textContent = `S/ ${total.toFixed(2)}`;

    contadorCarrito.textContent = cantidadTotal;

}

// =====================================
// CAMBIAR CANTIDAD
// =====================================

function cambiarCantidad(id, cambio) {

    const item = carrito.find(
        producto => Number(producto.IdProducto) === Number(id)
    );

    if (!item) return;

    const nuevaCantidad = item.cantidad + cambio;

    if (nuevaCantidad <= 0) {
        eliminarDelCarrito(id);
        return;
    }

    if (nuevaCantidad > item.Stock) {
        mostrarMensaje(
            "Has alcanzado el stock máximo disponible.",
            "error"
        );
        return;
    }

    item.cantidad = nuevaCantidad;

    guardarCarrito();
    mostrarCarrito();
}

// =====================================
// ELIMINAR PRODUCTO DEL CARRITO
// =====================================

function eliminarDelCarrito(id) {

    carrito = carrito.filter(
        item => Number(item.IdProducto) !== Number(id)
    );

    guardarCarrito();
    mostrarCarrito();
}

// =====================================
// VACIAR CARRITO
// =====================================

document.getElementById("btnVaciarCarrito")
    .addEventListener("click", () => {

        if (carrito.length === 0) return;

        const confirmar = confirm(
            "¿Estás seguro de que deseas vaciar el carrito?"
        );

        if (!confirmar) return;

        carrito = [];

        guardarCarrito();
        mostrarCarrito();
    });

// =====================================
// BUSCAR Y FILTRAR
// =====================================

function filtrarProductos() {

    const texto = buscarProducto.value.trim().toLowerCase();

    const categoriaSeleccionada = filtroCategoria.value;

    const resultado = productos.filter(producto => {

        const coincideNombre =
            (producto.Nombre || "").toLowerCase().includes(texto);

        const coincideDescripcion =
            (producto.Descripcion || "").toLowerCase().includes(texto);

        const coincideCategoria =
            !categoriaSeleccionada ||
            producto.Categoria === categoriaSeleccionada;

        return (
            (coincideNombre || coincideDescripcion) &&
            coincideCategoria
        );
    });

    mostrarProductos(resultado);
}

buscarProducto.addEventListener("input", filtrarProductos);

filtroCategoria.addEventListener("change", filtrarProductos);

// =====================================
// INICIAR
// =====================================

mostrarCarrito();
cargarProductos();
