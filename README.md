# TechStore

Sistema web demostrativo para una tienda de productos tecnológicos. El
proyecto incluye un frontend para visualizar y gestionar información de
la tienda y un backend con una API REST organizada bajo el patrón
Modelo--Vista--Controlador (MVC), conectada a Microsoft SQL Server.

## Descripción del proyecto

TechStore representa una tienda de productos tecnológicos, como laptops,
memorias RAM, unidades SSD, teclados, mouse y accesorios. La solución
organiza la información de la tienda y permite administrar los
siguientes recursos:

-   Categorías
-   Productos
-   Clientes
-   Usuarios
-   Pedidos
-   Detalles de pedido

También incluye un módulo de inicio de sesión.

## Tecnologías utilizadas

-   **Frontend:** HTML, JavaScript y Tailwind CSS.
-   **Backend:** PHP.
-   **Base de datos:** Microsoft SQL Server.
-   **Acceso a datos:** PDO_SQLSRV.
-   **Gestión de dependencias PHP:** Composer.
-   **Variables de entorno:** `vlucas/phpdotenv`.
-   **Pruebas de API:** Postman.
-   **Entorno local:** XAMPP.

## Arquitectura del backend

El backend separa las responsabilidades mediante:

-   **Models:** acceso y operaciones sobre los datos.
-   **Controllers:** validación y procesamiento de las solicitudes.
-   **Routes:** recepción y direccionamiento de las solicitudes HTTP.
-   **Config:** configuración de la conexión a la base de datos.

Los recursos comparten el punto de entrada de rutas, pero cada módulo
cuenta con su propio modelo y controlador.

## Estructura general del proyecto

``` text
TECHSTORE/
├── backend/
│   ├── api/
│   │   └── routes/
│   │       └── routes.php
│   ├── app/
│   │   ├── Controllers/
│   │   └── Models/
│   └── config/
│       └── database.php
├── database/
│   └── BD_TechStore.sql
├── frontend/
│   ├── src/
│   │   ├── js/
│   │   ├── *.html
│   │   └── styles.css
│   └── dist/
├── .env
├── composer.json
├── composer.lock
└── README.md
```

> La ubicación de `vendor/`, `composer.json`, `composer.lock` y `.env`
> debe mantenerse de acuerdo con la configuración real del proyecto. No
> se debe subir la carpeta `vendor/` al repositorio si la indicación de
> entrega solicita excluir dependencias.

## Requisitos previos

Antes de ejecutar el proyecto, se requiere:

-   XAMPP con Apache.
-   PHP compatible con las dependencias del proyecto.
-   Microsoft SQL Server.
-   Controlador PHP para SQL Server (`PDO_SQLSRV`).
-   Composer.
-   Node.js y npm.

## Configuración de la base de datos

1.  Iniciar SQL Server y conectarse a la instancia configurada para el
    proyecto.
2.  Abrir el archivo `database/BD_TechStore.sql` en SQL Server
    Management Studio.
3.  Revisar el contenido del script antes de ejecutarlo.

**Advertencia:** el script de creación incluido elimina la base de datos
`BD_TechStore` si ya existe y la vuelve a crear. Ejecutarlo reemplazará
la base actual y sus datos. Realizar una copia de seguridad antes de
ejecutarlo.

El script crea las tablas principales, relaciones, restricciones,
índices, datos de prueba y el procedimiento almacenado
`sp_ConsultarPedidos`.

## Configuración del backend

Configurar las variables de entorno de acuerdo con la instancia local de
SQL Server. Ejemplo de configuración:

``` env
DB_SERVER="NOMBRE_DE_SERVIDOR\\NOMBRE_DE_INSTANCIA"
DB_DATABASE="BD_TechStore"
DB_TRUST_CERTIFICATE=true
```

Reemplazar `NOMBRE_DE_SERVIDOR\\NOMBRE_DE_INSTANCIA` por el nombre real
de la instancia. No publicar credenciales privadas ni archivos `.env`
con información sensible.

Instalar las dependencias PHP con Composer desde la carpeta que contiene
el archivo `composer.json`:

``` bash
composer install
```

## Configuración del frontend

Desde la carpeta `frontend`, instalar las dependencias:

``` bash
npm install
```

Generar los archivos CSS de producción:

``` bash
npm run build
```

## Ejecución local

1.  Colocar la carpeta `TECHSTORE` dentro de `C:\xampp\htdocs\`.
2.  Iniciar Apache desde XAMPP.
3.  Verificar que SQL Server esté activo y que la base `BD_TechStore`
    exista.
4.  Comprobar la configuración de conexión del backend.
5.  Abrir el frontend desde el navegador, por ejemplo:

``` text
http://localhost/TECHSTORE/frontend/src/login.html
```

Si la página principal del proyecto se encuentra en otra ruta, utilizar
la ruta correspondiente dentro de `frontend`.

## API

La API utiliza el siguiente punto de entrada:

``` text
http://localhost/TECHSTORE/backend/api/routes/routes.php
```

Cada recurso se selecciona mediante el parámetro `recurso`.

  Recurso              Identificador
  -------------------- ---------------
  Categorías           `categorias`
  Productos            `productos`
  Clientes             `clientes`
  Usuarios             `usuarios`
  Pedidos              `pedidos`
  Detalles de pedido   `detalles`
  Inicio de sesión     `login`

### Operaciones CRUD

Para los seis recursos principales se implementan las operaciones
siguientes:

  Método   Operación           Ejemplo de ruta
  -------- ------------------- ----------------------------
  GET      Listar registros    `?recurso=categorias`
  GET      Consultar por ID    `?recurso=categorias&id=1`
  POST     Registrar           `?recurso=categorias`
  PUT      Actualizar por ID   `?recurso=categorias&id=1`
  DELETE   Eliminar por ID     `?recurso=categorias&id=1`

Los ejemplos muestran el formato general del enrutamiento. Los nombres
de los campos del cuerpo JSON dependen del recurso y deben enviarse
según lo esperado por cada controlador.

## Pruebas de la API

Las solicitudes se verificaron utilizando Postman. Para el documento de
evidencias se seleccionaron dos pruebas por cada uno de los seis
recursos principales, de acuerdo con la indicación del docente.

Las pruebas documentadas incluyen:

-   Categorías: GET y POST.
-   Productos: GET y PUT.
-   Clientes: GET y POST.
-   Usuarios: GET y POST.
-   Pedidos: GET y POST.
-   Detalles de pedido: GET y POST.

Las demás operaciones CRUD también forman parte de los endpoints
implementados en el backend, aunque no todas se incluyan como capturas
en el documento de evidencias.

## Códigos HTTP utilizados

-   `200 OK`: solicitud procesada correctamente.
-   `201 Created`: registro creado correctamente.
-   `400 Bad Request`: solicitud con datos incorrectos o incompletos.
-   `404 Not Found`: recurso o registro no encontrado.
-   `500 Internal Server Error`: error interno del servidor.

## Consideraciones de seguridad y entrega

-   No publicar contraseñas, hashes, credenciales de SQL Server ni
    archivos `.env`.
-   No incluir dependencias instaladas (`vendor/` o `node_modules/`) si
    las instrucciones de entrega indican excluirlas.
-   Mantener `composer.json`, `composer.lock` y `package.json` para
    permitir la instalación de dependencias.
-   Revisar el script SQL antes de ejecutarlo, porque recrea la base de
    datos.
-   Adjuntar el documento de evidencias de Postman junto con el proyecto
    y el script SQL, según las indicaciones del docente.

## Autoría

**Proyecto:** TechStore\
**Curso:** \[Completar nombre del curso\]\
**NRC:** \[Completar NRC\]\
**Estudiante:** \[Completar nombre del estudiante\]\
**Docente:** \[Completar nombre del docente\]\
**Periodo:** \[Completar periodo académico\]
