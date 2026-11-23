
const API_URL = "../../backend/api/routes/routes.php?recurso=clientes";

const formCliente = document.getElementById("formCliente");

const idCliente = document.getElementById("idCliente");
const nombres = document.getElementById("nombres");
const apellidos = document.getElementById("apellidos");
const correo = document.getElementById("correo");
const telefono = document.getElementById("telefono");

const tablaClientes = document.getElementById("tablaClientes");
const mensajeClientes = document.getElementById("mensajeClientes");

const tituloFormulario = document.getElementById("tituloFormulario");
const btnRegistrar = document.getElementById("btnRegistrar");
const btnCancelar = document.getElementById("btnCancelar");
const btnActualizar = document.getElementById("btnActualizar");

// LISTAR CLIENTES
async function listarClientes() {
    tablaClientes.innerHTML = `
        <tr>
            <td colspan="6" class="p-4 text-center">
                Cargando clientes...
            </td>
        </tr>
    `;

    try {
        const respuesta = await fetch(API_URL);
        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(
                resultado.mensaje || resultado.message ||
                "No se pudieron cargar los clientes."
            );
        }

        const clientes = resultado.data || [];

        tablaClientes.innerHTML = "";

        if (clientes.length === 0) {
            tablaClientes.innerHTML = `
                <tr>
                    <td colspan="6" class="p-4 text-center text-slate-500">
                        No hay clientes registrados.
                    </td>
                </tr>
            `;
            mensajeClientes.textContent = "No hay clientes registrados.";
            return;
        }

        clientes.forEach(cliente => {
            const fila = document.createElement("tr");
            fila.className = "border-b hover:bg-slate-50";

            const valores = [
                cliente.IdCliente,
                cliente.Nombres,
                cliente.Apellidos,
                cliente.Correo,
                cliente.Telefono
            ];

            valores.forEach(valor => {
                const celda = document.createElement("td");
                celda.className = "p-4";
                celda.textContent = valor ?? "";
                fila.appendChild(celda);
            });

            const acciones = document.createElement("td");
            acciones.className = "p-4 whitespace-nowrap";

            const btnEditar = document.createElement("button");
            btnEditar.textContent = "Editar";
            btnEditar.className =
                "bg-amber-500 hover:bg-amber-600 text-white px-3 py-2 rounded-lg mr-2";

            btnEditar.addEventListener("click", () => {
                editarCliente(cliente);
            });

            const btnEliminar = document.createElement("button");
            btnEliminar.textContent = "Eliminar";
            btnEliminar.className =
                "bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg";

            btnEliminar.addEventListener("click", () => {
                eliminarCliente(cliente.IdCliente);
            });

            acciones.append(btnEditar, btnEliminar);
            fila.appendChild(acciones);

            tablaClientes.appendChild(fila);
        });

        mensajeClientes.textContent =
            `Se encontraron ${clientes.length} clientes.`;

    } catch (error) {
        console.error(error);

        tablaClientes.innerHTML = `
            <tr>
                <td colspan="6" class="p-4 text-center text-red-500">
                    Error al cargar los clientes.
                </td>
            </tr>
        `;

        mensajeClientes.textContent = error.message;
    }
}

// REGISTRAR O ACTUALIZAR CLIENTE
formCliente.addEventListener("submit", async (evento) => {
    evento.preventDefault();

    const datos = {
        nombres: nombres.value.trim(),
        apellidos: apellidos.value.trim(),
        correo: correo.value.trim(),
        telefono: telefono.value.trim()
    };

    if (
        !datos.nombres ||
        !datos.apellidos ||
        !datos.correo ||
        !datos.telefono
    ) {
        alert("Completa todos los campos.");
        return;
    }

    const estaEditando = idCliente.value !== "";

    const url = estaEditando
        ? `${API_URL}&id=${encodeURIComponent(idCliente.value)}`
        : API_URL;

    try {
        const respuesta = await fetch(url, {
            method: estaEditando ? "PUT" : "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(datos)
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(
                resultado.mensaje || resultado.message ||
                "No se pudo guardar el cliente."
            );
        }

        alert(
            resultado.mensaje || resultado.message ||
            (estaEditando
                ? "Cliente actualizado correctamente."
                : "Cliente registrado correctamente.")
        );

        limpiarFormulario();
        listarClientes();

    } catch (error) {
        console.error(error);
        alert(error.message);
    }
});

// EDITAR CLIENTE
function editarCliente(cliente) {
    idCliente.value = cliente.IdCliente;
    nombres.value = cliente.Nombres ?? "";
    apellidos.value = cliente.Apellidos ?? "";
    correo.value = cliente.Correo ?? "";
    telefono.value = cliente.Telefono ?? "";

    tituloFormulario.textContent = "Actualizar cliente";
    btnRegistrar.textContent = "Guardar cambios";
    btnCancelar.classList.remove("hidden");

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}

// ELIMINAR CLIENTE
async function eliminarCliente(id) {
    const confirmar = confirm(
        "¿Estás seguro de eliminar este cliente?"
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
            throw new Error(
                resultado.mensaje || resultado.message ||
                "No se pudo eliminar el cliente."
            );
        }

        alert(resultado.mensaje || resultado.message ||
            "Cliente eliminado correctamente.");

        listarClientes();

    } catch (error) {
        console.error(error);
        alert(error.message);
    }
}

// LIMPIAR FORMULARIO
function limpiarFormulario() {
    formCliente.reset();
    idCliente.value = "";

    tituloFormulario.textContent = "Registrar cliente";
    btnRegistrar.textContent = "Registrar cliente";

    btnCancelar.classList.add("hidden");
}

// EVENTOS
btnCancelar.addEventListener("click", limpiarFormulario);
btnActualizar.addEventListener("click", listarClientes);

// INICIAR
listarClientes();
