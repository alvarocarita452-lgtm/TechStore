
const API_URL = "../../backend/api/routes/routes.php?recurso=usuarios";

const formUsuario = document.getElementById("formUsuario");
const tablaUsuarios = document.getElementById("tablaUsuarios");
const cantidadUsuarios = document.getElementById("cantidadUsuarios");
const mensajeUsuario = document.getElementById("mensajeUsuario");

const tituloFormulario = document.getElementById("tituloFormulario");
const btnGuardar = document.getElementById("btnGuardar");
const btnCancelar = document.getElementById("btnCancelar");

const inputNombres = document.getElementById("nombres");
const inputApellidos = document.getElementById("apellidos");
const inputCorreo = document.getElementById("correo");
const inputContrasena = document.getElementById("contrasena");
const inputRol = document.getElementById("rol");
const ayudaContrasena = document.getElementById("ayudaContrasena");

let idUsuarioEditar = null;

// ESCAPAR TEXTO PARA MOSTRARLO EN HTML
function escaparHTML(valor) {
    return String(valor ?? "").replace(/[&<>"']/g, caracter => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    })[caracter]);
}

// MOSTRAR MENSAJES
function mostrarMensaje(mensaje, tipo = "exito") {

    mensajeUsuario.textContent = mensaje;

    mensajeUsuario.classList.remove(
        "hidden",
        "bg-green-100",
        "text-green-800",
        "bg-red-100",
        "text-red-800"
    );

    if (tipo === "exito") {
        mensajeUsuario.classList.add("bg-green-100", "text-green-800");
    } else {
        mensajeUsuario.classList.add("bg-red-100", "text-red-800");
    }

    window.scrollTo({ top: 0, behavior: "smooth" });
}

// PETICIÓN A LA API
async function solicitarAPI(url, opciones = {}) {

    const respuesta = await fetch(url, {
        ...opciones,
        headers: {
            "Content-Type": "application/json",
            ...opciones.headers
        }
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || resultado.success === false) {
        throw new Error(
            resultado.message ||
            resultado.mensaje ||
            "Ocurrió un error al procesar la solicitud."
        );
    }

    return resultado;
}

// LISTAR USUARIOS
async function listarUsuarios() {

    try {

        const resultado = await solicitarAPI(API_URL);
        const usuarios = resultado.data || [];

        cantidadUsuarios.textContent =
            `Se encontraron ${usuarios.length} usuarios.`;

        if (usuarios.length === 0) {

            tablaUsuarios.innerHTML = `
                <tr>
                    <td colspan="6" class="p-4 text-center text-slate-500">
                        No hay usuarios registrados.
                    </td>
                </tr>
            `;

            return;
        }

        tablaUsuarios.innerHTML = usuarios.map(usuario => `

            <tr class="border-b border-slate-200 hover:bg-slate-50">

                <td class="p-3">
                    ${escaparHTML(usuario.IdUsuario)}
                </td>

                <td class="p-3">
                    ${escaparHTML(usuario.Nombres)}
                </td>

                <td class="p-3">
                    ${escaparHTML(usuario.Apellidos)}
                </td>

                <td class="p-3">
                    ${escaparHTML(usuario.Correo)}
                </td>

                <td class="p-3">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        ${usuario.Rol === "Administrador"
                            ? "bg-purple-100 text-purple-700"
                            : "bg-cyan-100 text-cyan-700"}">
                        ${escaparHTML(usuario.Rol)}
                    </span>
                </td>

                <td class="p-3 whitespace-nowrap">

                    <button
                        type="button"
                        onclick="editarUsuario(${Number(usuario.IdUsuario)})"
                        class="bg-cyan-500 hover:bg-cyan-600 text-white px-3 py-2 rounded-lg font-semibold">
                        Editar
                    </button>

                    <button
                        type="button"
                        onclick="eliminarUsuario(${Number(usuario.IdUsuario)})"
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg font-semibold">
                        Eliminar
                    </button>

                </td>

            </tr>

        `).join("");

    } catch (error) {

        cantidadUsuarios.textContent =
            "No se pudieron cargar los usuarios.";

        tablaUsuarios.innerHTML = `
            <tr>
                <td colspan="6" class="p-4 text-center text-red-600">
                    ${escaparHTML(error.message)}
                </td>
            </tr>
        `;
    }
}

// REGISTRAR O ACTUALIZAR USUARIO
formUsuario.addEventListener("submit", async (evento) => {

    evento.preventDefault();

    const datos = {
        nombres: inputNombres.value.trim(),
        apellidos: inputApellidos.value.trim(),
        correo: inputCorreo.value.trim(),
        rol: inputRol.value
    };

    if (
        !datos.nombres ||
        !datos.apellidos ||
        !datos.correo ||
        !datos.rol
    ) {
        mostrarMensaje(
            "Completa todos los campos obligatorios.",
            "error"
        );
        return;
    }

    // VALIDAR CONTRASEÑA SOLAMENTE AL REGISTRAR
    if (idUsuarioEditar === null) {

        datos.contrasena = inputContrasena.value;

        if (datos.contrasena.length < 8) {
            mostrarMensaje(
                "La contraseña debe tener al menos 8 caracteres.",
                "error"
            );
            return;
        }
    }

    try {

        let url = API_URL;
        let metodo = "POST";

        if (idUsuarioEditar !== null) {
            url += `&id=${idUsuarioEditar}`;
            metodo = "PUT";
        }

        const resultado = await solicitarAPI(url, {
            method: metodo,
            body: JSON.stringify(datos)
        });

        const mensaje = idUsuarioEditar !== null
            ? "Usuario actualizado correctamente."
            : "Usuario registrado correctamente.";

        mostrarMensaje(
            resultado.message ||
            resultado.mensaje ||
            mensaje
        );

        cancelarEdicion();
        await listarUsuarios();

    } catch (error) {

        mostrarMensaje(error.message, "error");
    }
});

// EDITAR USUARIO
async function editarUsuario(id) {

    try {

        const resultado = await solicitarAPI(`${API_URL}&id=${id}`);

        const usuario = resultado.data;

        if (!usuario) {
            mostrarMensaje("No se encontró el usuario.", "error");
            return;
        }

        idUsuarioEditar = Number(usuario.IdUsuario);

        inputNombres.value = usuario.Nombres || "";
        inputApellidos.value = usuario.Apellidos || "";
        inputCorreo.value = usuario.Correo || "";
        inputRol.value = usuario.Rol || "";

        // LA CONTRASEÑA NO SE RECUPERA NI SE MODIFICA
        inputContrasena.value = "";
        inputContrasena.required = false;

        tituloFormulario.textContent = "Actualizar usuario";
        btnGuardar.textContent = "Guardar cambios";

        btnCancelar.classList.remove("hidden");

        ayudaContrasena.textContent =
            "La contraseña no se modifica desde este formulario.";

        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });

    } catch (error) {

        mostrarMensaje(error.message, "error");
    }
}

// CANCELAR EDICIÓN
function cancelarEdicion() {

    idUsuarioEditar = null;

    formUsuario.reset();

    inputContrasena.value = "";
    inputContrasena.required = false;

    tituloFormulario.textContent = "Registrar usuario";
    btnGuardar.textContent = "Registrar usuario";

    btnCancelar.classList.add("hidden");

    ayudaContrasena.textContent =
        "La contraseña es obligatoria al registrar un usuario.";
}

// BOTÓN CANCELAR
btnCancelar.addEventListener("click", cancelarEdicion);

// ELIMINAR USUARIO
async function eliminarUsuario(id) {

    const confirmar = confirm(
        `¿Estás seguro de eliminar el usuario con ID ${id}?`
    );

    if (!confirmar) {
        return;
    }

    try {

        const resultado = await solicitarAPI(`${API_URL}&id=${id}`, {
            method: "DELETE"
        });

        mostrarMensaje(
            resultado.message ||
            resultado.mensaje ||
            "Usuario eliminado correctamente."
        );

        if (idUsuarioEditar === Number(id)) {
            cancelarEdicion();
        }

        await listarUsuarios();

    } catch (error) {

        mostrarMensaje(error.message, "error");
    }
}

// INICIAR MÓDULO
listarUsuarios();
