
const API_URL = "../../backend/api/routes/routes.php?recurso=productos";
const API_CATEGORIAS = "../../backend/api/routes/routes.php?recurso=categorias";

// ELEMENTOS HTML
const tablaProductos = document.getElementById("tablaProductos");
const mensaje = document.getElementById("mensaje");
const formulario = document.getElementById("formProducto");
const botonRegistrar = document.getElementById("btnRegistrar");
const botonCancelar = document.getElementById("btnCancelar");
const tituloFormulario = document.getElementById("tituloFormulario");

// ID del producto que se está editando
let idProductoEditar = null;

// MOSTRAR MENSAJES
function mostrarMensaje(texto, tipo = "success") {
    mensaje.textContent = texto;

    mensaje.className = tipo === "success"
        ? "mb-4 text-sm font-semibold text-green-600"
        : "mb-4 text-sm font-semibold text-red-600";
}

// LISTAR PRODUCTOS
async function listarProductos() {

    tablaProductos.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-8">
                Cargando productos...
            </td>
        </tr>
    `;

    try {
        const respuesta = await fetch(API_URL);
        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.message || "No se pudieron cargar los productos");
        }

        const productos = resultado.data;

        if (!productos || productos.length === 0) {
            tablaProductos.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-8 text-slate-500">
                        No hay productos registrados.
                    </td>
                </tr>
            `;
            return;
        }

        tablaProductos.innerHTML = "";

        productos.forEach(producto => {

            const fila = document.createElement("tr");
            fila.className = "border-t border-slate-200 hover:bg-slate-50";

            // DATOS DEL PRODUCTO
            const valores = [
                producto.IdProducto,
                producto.Nombre,
                producto.Descripcion ?? "",
                `S/ ${Number(producto.Precio).toFixed(2)}`,
                producto.Stock,
                producto.Categoria ?? ""
            ];

            valores.forEach((valor, indice) => {

                const celda = document.createElement("td");

                celda.className = indice === 1
                    ? "px-6 py-4 font-semibold"
                    : "px-6 py-4";

                celda.textContent = valor;

                fila.appendChild(celda);
            });

            // ESTADO DEL PRODUCTO
            const activo = Number(producto.Estado) === 1;

            const celdaEstado = document.createElement("td");
            celdaEstado.className = "px-6 py-4 text-center";

            const etiquetaEstado = document.createElement("span");

            etiquetaEstado.textContent = activo ? "Activo" : "Inactivo";

            etiquetaEstado.className = activo
                ? "bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold"
                : "bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-semibold";

            celdaEstado.appendChild(etiquetaEstado);
            fila.appendChild(celdaEstado);

            // COLUMNA DE ACCIONES
            const celdaAcciones = document.createElement("td");
            celdaAcciones.className = "px-6 py-4";

            const contenedor = document.createElement("div");
            contenedor.className = "flex justify-center gap-2 flex-wrap";

            // BOTÓN EDITAR
            const botonEditar = document.createElement("button");

            botonEditar.textContent = "Editar";
            botonEditar.className =
                "bg-amber-500 hover:bg-amber-600 text-white px-3 py-2 rounded-lg text-sm font-semibold";

            botonEditar.addEventListener("click", () => {
                editarProducto(producto.IdProducto);
            });

            // BOTÓN ACTIVAR / DESACTIVAR
            const botonEstado = document.createElement("button");

            botonEstado.textContent = activo ? "Desactivar" : "Activar";

            botonEstado.className = activo
                ? "bg-slate-600 hover:bg-slate-700 text-white px-3 py-2 rounded-lg text-sm font-semibold"
                : "bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm font-semibold";

            botonEstado.addEventListener("click", () => {
                cambiarEstadoProducto(producto.IdProducto, activo ? 0 : 1);
            });

            // BOTÓN ELIMINAR
            const botonEliminar = document.createElement("button");

            botonEliminar.textContent = "Eliminar";
            botonEliminar.className =
                "bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg text-sm font-semibold";

            botonEliminar.addEventListener("click", () => {
                eliminarProducto(producto.IdProducto);
            });

            contenedor.appendChild(botonEditar);
            contenedor.appendChild(botonEstado);
            contenedor.appendChild(botonEliminar);

            celdaAcciones.appendChild(contenedor);
            fila.appendChild(celdaAcciones);

            tablaProductos.appendChild(fila);
        });

    } catch (error) {

        tablaProductos.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-8 text-red-600">
                    Error al cargar los productos.
                </td>
            </tr>
        `;

        mostrarMensaje(error.message, "error");
        console.error(error);
    }
}

// CARGAR CATEGORÍAS
async function cargarCategorias() {

    const select = document.getElementById("idCategoria");

    try {
        const respuesta = await fetch(API_CATEGORIAS);
        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error("No se pudieron cargar las categorías");
        }

        resultado.data.forEach(categoria => {

            const opcion = document.createElement("option");

            opcion.value = categoria.IdCategoria;
            opcion.textContent = categoria.Nombre;

            select.appendChild(opcion);
        });

    } catch (error) {
        mostrarMensaje(error.message, "error");
    }
}

// REGISTRAR O ACTUALIZAR PRODUCTO
formulario.addEventListener("submit", async (event) => {

    event.preventDefault();

    const producto = {
        nombre: document.getElementById("nombre").value.trim(),
        descripcion: document.getElementById("descripcion").value.trim(),
        precio: Number(document.getElementById("precio").value),
        stock: Number(document.getElementById("stock").value),
        idCategoria: Number(document.getElementById("idCategoria").value)
    };

    try {
        botonRegistrar.disabled = true;

        let respuesta;

        if (idProductoEditar === null) {

            // REGISTRAR
            botonRegistrar.textContent = "Registrando...";

            respuesta = await fetch(API_URL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(producto)
            });

        } else {

            // ACTUALIZAR
            botonRegistrar.textContent = "Actualizando...";

            respuesta = await fetch(`${API_URL}&id=${idProductoEditar}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(producto)
            });
        }

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.message || "No se pudo guardar el producto");
        }

        mostrarMensaje(resultado.message, "success");

        limpiarFormulario();

        await listarProductos();

    } catch (error) {

        mostrarMensaje(error.message, "error");

    } finally {

        botonRegistrar.disabled = false;

        botonRegistrar.textContent = idProductoEditar === null
            ? "Registrar producto"
            : "Actualizar producto";
    }
});

// EDITAR PRODUCTO
async function editarProducto(id) {

    try {
        const respuesta = await fetch(`${API_URL}&id=${id}`);
        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.message || "No se pudo obtener el producto");
        }

        const producto = resultado.data;

        document.getElementById("nombre").value = producto.Nombre;
        document.getElementById("descripcion").value = producto.Descripcion ?? "";
        document.getElementById("precio").value = producto.Precio;
        document.getElementById("stock").value = producto.Stock;
        document.getElementById("idCategoria").value = producto.IdCategoria;

        idProductoEditar = Number(id);

        tituloFormulario.textContent = "Editar producto";
        botonRegistrar.textContent = "Actualizar producto";

        botonCancelar.classList.remove("hidden");

        mostrarMensaje(`Editando producto: ${producto.Nombre}`, "success");

        formulario.scrollIntoView({
            behavior: "smooth",
            block: "center"
        });

    } catch (error) {
        mostrarMensaje(error.message, "error");
    }
}

// CAMBIAR ESTADO DEL PRODUCTO
async function cambiarEstadoProducto(id, nuevoEstado) {

    const accion = nuevoEstado === 1 ? "activar" : "desactivar";

    const confirmar = confirm(
        `¿Estás seguro de ${accion} el producto con ID ${id}?`
    );

    if (!confirmar) {
        return;
    }

    try {
        const respuesta = await fetch(
            `${API_URL}&id=${id}&accion=estado`,
            {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    estado: nuevoEstado
                })
            }
        );

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.message || "No se pudo cambiar el estado del producto");
        }

        mostrarMensaje(resultado.message, "success");

        await listarProductos();

    } catch (error) {
        mostrarMensaje(error.message, "error");
        console.error(error);
    }
}

// ELIMINAR PRODUCTO
async function eliminarProducto(id) {

    const confirmar = confirm(
        `¿Estás seguro de eliminar el producto con ID ${id}?`
    );

    if (!confirmar) {
        return;
    }

    try {
        const respuesta = await fetch(`${API_URL}&id=${id}`, {
            method: "DELETE"
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.message || "No se pudo eliminar el producto");
        }

        mostrarMensaje(resultado.message, "success");

        if (idProductoEditar === Number(id)) {
            limpiarFormulario();
        }

        await listarProductos();

    } catch (error) {
        mostrarMensaje(error.message, "error");
    }
}

// LIMPIAR FORMULARIO
function limpiarFormulario() {

    formulario.reset();

    idProductoEditar = null;

    tituloFormulario.textContent = "Registrar producto";
    botonRegistrar.textContent = "Registrar producto";

    botonCancelar.classList.add("hidden");
}

// CANCELAR EDICIÓN
botonCancelar.addEventListener("click", () => {

    limpiarFormulario();

    mostrarMensaje("Edición cancelada", "success");
});

// ACTUALIZAR LISTADO
document.getElementById("btnActualizar")
    .addEventListener("click", listarProductos);

// INICIAR SISTEMA
async function iniciarModulo() {

    await cargarCategorias();
    await listarProductos();

}

iniciarModulo();
