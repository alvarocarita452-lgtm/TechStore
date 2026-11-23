
const API_URL = "../../backend/api/routes/routes.php?recurso=categorias";

// Elementos del HTML
const formCategoria = document.getElementById("formCategoria");
const idCategoria = document.getElementById("idCategoria");
const nombre = document.getElementById("nombre");
const descripcion = document.getElementById("descripcion");

const tablaCategorias = document.getElementById("tablaCategorias");
const mensajeCategorias = document.getElementById("mensajeCategorias");

const tituloFormulario = document.getElementById("tituloFormulario");
const btnRegistrar = document.getElementById("btnRegistrar");
const btnCancelar = document.getElementById("btnCancelar");
const btnActualizar = document.getElementById("btnActualizar");

// LISTAR CATEGORÍAS
async function listarCategorias() {
    tablaCategorias.innerHTML = `
        <tr>
            <td colspan="4" class="p-4 text-center">
                Cargando categorías...
            </td>
        </tr>
    `;

    try {
        const respuesta = await fetch(API_URL);
        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.mensaje || resultado.message ||
                "No se pudieron cargar las categorías.");
        }

        const categorias = resultado.data || [];

        tablaCategorias.innerHTML = "";

        if (categorias.length === 0) {
            tablaCategorias.innerHTML = `
                <tr>
                    <td colspan="4" class="p-4 text-center text-slate-500">
                        No hay categorías registradas.
                    </td>
                </tr>
            `;
            return;
        }

        categorias.forEach(categoria => {
            const fila = document.createElement("tr");
            fila.className = "border-b hover:bg-slate-50";

            const id = categoria.IdCategoria;
            const nombreCategoria = categoria.Nombre ?? "";
            const descripcionCategoria = categoria.Descripcion ?? "";

            const celdaId = document.createElement("td");
            celdaId.className = "p-4";
            celdaId.textContent = id;

            const celdaNombre = document.createElement("td");
            celdaNombre.className = "p-4 font-semibold";
            celdaNombre.textContent = nombreCategoria;

            const celdaDescripcion = document.createElement("td");
            celdaDescripcion.className = "p-4";
            celdaDescripcion.textContent = descripcionCategoria;

            const celdaAcciones = document.createElement("td");
            celdaAcciones.className = "p-4";

            const btnEditar = document.createElement("button");
            btnEditar.textContent = "Editar";
            btnEditar.className =
                "bg-amber-500 hover:bg-amber-600 text-white px-3 py-2 rounded-lg mr-2";

            btnEditar.addEventListener("click", () => {
                editarCategoria(
                    id,
                    nombreCategoria,
                    descripcionCategoria
                );
            });

            const btnEliminar = document.createElement("button");
            btnEliminar.textContent = "Eliminar";
            btnEliminar.className =
                "bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg";

            btnEliminar.addEventListener("click", () => {
                eliminarCategoria(id);
            });

            celdaAcciones.append(btnEditar, btnEliminar);
            fila.append(
                celdaId,
                celdaNombre,
                celdaDescripcion,
                celdaAcciones
            );

            tablaCategorias.appendChild(fila);
        });

        mensajeCategorias.textContent =
            `Se encontraron ${categorias.length} categorías.`;

    } catch (error) {
        console.error("Error:", error);

        tablaCategorias.innerHTML = `
            <tr>
                <td colspan="4" class="p-4 text-center text-red-500">
                    Error al cargar las categorías.
                </td>
            </tr>
        `;

        mensajeCategorias.textContent = error.message;
    }
}

// REGISTRAR O ACTUALIZAR
formCategoria.addEventListener("submit", async (evento) => {
    evento.preventDefault();

    const datos = {
        nombre: nombre.value.trim(),
        descripcion: descripcion.value.trim()
    };

    if (!datos.nombre || !datos.descripcion) {
        alert("Completa todos los campos.");
        return;
    }

    const estaEditando = idCategoria.value !== "";

    const url = estaEditando
        ? `${API_URL}&id=${encodeURIComponent(idCategoria.value)}`
        : API_URL;

    const metodo = estaEditando ? "PUT" : "POST";

    try {
        const respuesta = await fetch(url, {
            method: metodo,
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(datos)
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.mensaje || resultado.message ||
                "No se pudo guardar la categoría.");
        }

        alert(resultado.mensaje || resultado.message ||
            (estaEditando
                ? "Categoría actualizada correctamente."
                : "Categoría registrada correctamente."));

        limpiarFormulario();
        listarCategorias();

    } catch (error) {
        console.error(error);
        alert(error.message);
    }
});

// CARGAR DATOS PARA EDITAR
function editarCategoria(id, nombreActual, descripcionActual) {
    idCategoria.value = id;
    nombre.value = nombreActual;
    descripcion.value = descripcionActual;

    tituloFormulario.textContent = "Actualizar categoría";
    btnRegistrar.textContent = "Guardar cambios";
    btnCancelar.classList.remove("hidden");

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}

// ELIMINAR CATEGORÍA
async function eliminarCategoria(id) {
    const confirmar = confirm(
        "¿Estás seguro de eliminar esta categoría?"
    );

    if (!confirmar) return;

    try {
        const respuesta = await fetch(
            `${API_URL}&id=${encodeURIComponent(id)}`,
            {
                method: "DELETE"
            }
        );

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.mensaje || resultado.message ||
                "No se pudo eliminar la categoría.");
        }

        alert(resultado.mensaje || resultado.message ||
            "Categoría eliminada correctamente.");

        listarCategorias();

    } catch (error) {
        console.error(error);
        alert(error.message);
    }
}

// LIMPIAR FORMULARIO
function limpiarFormulario() {
    formCategoria.reset();
    idCategoria.value = "";

    tituloFormulario.textContent = "Registrar categoría";
    btnRegistrar.textContent = "Registrar categoría";

    btnCancelar.classList.add("hidden");
}

// BOTÓN CANCELAR
btnCancelar.addEventListener("click", limpiarFormulario);

// BOTÓN ACTUALIZAR
btnActualizar.addEventListener("click", listarCategorias);

// INICIAR
listarCategorias();
