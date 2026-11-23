
const formulario = document.getElementById("formLogin");
const mensaje = document.getElementById("mensaje");
const boton = document.getElementById("btnLogin");

const API_LOGIN = "http://localhost/TECHSTORE/backend/api/routes/routes.php?recurso=login";

formulario.addEventListener("submit", async (event) => {
    event.preventDefault();

    const correo = document.getElementById("correo").value.trim();
    const contrasena = document.getElementById("contrasena").value;

    mensaje.textContent = "";
    boton.disabled = true;
    boton.textContent = "Verificando...";

    try {
        const respuesta = await fetch(API_LOGIN, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                correo: correo,
                contrasena: contrasena
            })
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.message || "No se pudo iniciar sesión");
        }

        // Guardar los datos básicos del usuario durante la sesión
        sessionStorage.setItem(
            "usuario",
            JSON.stringify(resultado.data)
        );

        mensaje.textContent = "¡Bienvenido a TechStore!";
        mensaje.className = "text-sm text-center text-green-600 font-semibold";

        boton.textContent = "Inicio exitoso";

        setTimeout(() => {
            window.location.href = "./dashboard.html";
        }, 1000);

    } catch (error) {

        mensaje.textContent = error.message;
        mensaje.className = "text-sm text-center text-red-600";

        boton.disabled = false;
        boton.textContent = "Iniciar sesión";
    }
});
