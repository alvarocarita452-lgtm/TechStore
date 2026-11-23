
const usuarioGuardado = sessionStorage.getItem("usuario");

// Verificar si existe un usuario en la sesión del navegador
if (!usuarioGuardado) {
    window.location.href = "./login.html";
} else {

    const usuario = JSON.parse(usuarioGuardado);

    document.getElementById("nombreUsuario").textContent =
        usuario.Nombres + " " + usuario.Apellidos;

    document.getElementById("rolUsuario").textContent =
        usuario.Rol;

    // Ocultar administración de usuarios a los vendedores
    if (usuario.Rol !== "Administrador") {
        document.getElementById("menuUsuarios").style.display = "none";
        document.getElementById("tarjetaUsuarios").style.display = "none";
    }
}

// Cerrar sesión
document.getElementById("btnCerrarSesion").addEventListener("click", () => {

    sessionStorage.removeItem("usuario");

    window.location.href = "./login.html";

});
