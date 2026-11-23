/*
    PROYECTO: TechStore
    BASE DE DATOS: BD_TechStore
    MOTOR: Microsoft SQL Server

    IMPORTANTE:
    Este script elimina BD_TechStore si ya existe y la crea nuevamente
    desde cero. Ejecutarlo solo si deseas reemplazar la base actual.
*/

USE master;
GO

IF DB_ID(N'BD_TechStore') IS NOT NULL
BEGIN
    ALTER DATABASE BD_TechStore
    SET SINGLE_USER WITH ROLLBACK IMMEDIATE;

    DROP DATABASE BD_TechStore;
END;
GO

CREATE DATABASE BD_TechStore;
GO

USE BD_TechStore;
GO

/* =========================================================
   1. CREACION DE TABLAS
   ========================================================= */

CREATE TABLE Categoria (
    IdCategoria INT IDENTITY(1,1) PRIMARY KEY,
    Nombre VARCHAR(50) NOT NULL UNIQUE,
    Descripcion VARCHAR(150)
);
GO

CREATE TABLE Producto (
    IdProducto INT IDENTITY(1,1) PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Descripcion VARCHAR(200),
    Precio DECIMAL(10,2) NOT NULL,
    Stock INT NOT NULL,
    IdCategoria INT NOT NULL,

    CONSTRAINT FK_Producto_Categoria
        FOREIGN KEY (IdCategoria)
        REFERENCES Categoria(IdCategoria),

    CONSTRAINT CK_Producto_Precio
        CHECK (Precio > 0),

    CONSTRAINT CK_Producto_Stock
        CHECK (Stock >= 0)
);
GO

CREATE TABLE Cliente (
    IdCliente INT IDENTITY(1,1) PRIMARY KEY,
    Nombres VARCHAR(80) NOT NULL,
    Apellidos VARCHAR(80) NOT NULL,
    Correo VARCHAR(100) NOT NULL UNIQUE,
    Telefono VARCHAR(15)
);
GO

CREATE TABLE Usuario (
    IdUsuario INT IDENTITY(1,1) PRIMARY KEY,
    Nombres VARCHAR(80) NOT NULL,
    Apellidos VARCHAR(80) NOT NULL,
    Correo VARCHAR(100) NOT NULL UNIQUE,
    Contrasena VARCHAR(255) NOT NULL,
    Rol VARCHAR(30) NOT NULL
);
GO

CREATE TABLE Pedido (
    IdPedido INT IDENTITY(1,1) PRIMARY KEY,
    FechaPedido DATETIME NOT NULL
        CONSTRAINT DF_Pedido_FechaPedido DEFAULT GETDATE(),
    Total DECIMAL(10,2) NOT NULL,
    Estado VARCHAR(30) NOT NULL,
    IdCliente INT NOT NULL,
    IdUsuario INT NOT NULL,

    CONSTRAINT FK_Pedido_Cliente
        FOREIGN KEY (IdCliente)
        REFERENCES Cliente(IdCliente),

    CONSTRAINT FK_Pedido_Usuario
        FOREIGN KEY (IdUsuario)
        REFERENCES Usuario(IdUsuario),

    CONSTRAINT CK_Pedido_Total
        CHECK (Total >= 0)
);
GO

CREATE TABLE DetallePedido (
    IdDetalle INT IDENTITY(1,1) PRIMARY KEY,
    IdPedido INT NOT NULL,
    IdProducto INT NOT NULL,
    Cantidad INT NOT NULL,
    PrecioUnitario DECIMAL(10,2) NOT NULL,

    CONSTRAINT FK_DetallePedido_Pedido
        FOREIGN KEY (IdPedido)
        REFERENCES Pedido(IdPedido),

    CONSTRAINT FK_DetallePedido_Producto
        FOREIGN KEY (IdProducto)
        REFERENCES Producto(IdProducto),

    CONSTRAINT CK_Detalle_Cantidad
        CHECK (Cantidad > 0),

    CONSTRAINT CK_Detalle_Precio
        CHECK (PrecioUnitario > 0)
);
GO

/* =========================================================
   2. INSERCION DE DATOS DE PRUEBA
   Maximo: 10 registros por tabla
   ========================================================= */

INSERT INTO Categoria (Nombre, Descripcion)
VALUES
    ('Laptops', 'Computadoras portatiles'),
    ('Componentes', 'Memorias RAM y componentes'),
    ('Almacenamiento', 'Discos SSD y almacenamiento'),
    ('Accesorios', 'Teclados, mouse y accesorios');
GO

INSERT INTO Producto
    (Nombre, Descripcion, Precio, Stock, IdCategoria)
VALUES
    ('Laptop ASUS TUF', 'Laptop para gaming', 2500.00, 10, 1),
    ('Memoria RAM 16GB', 'Memoria RAM DDR4', 180.00, 20, 2),
    ('SSD 1TB', 'Disco solido de 1TB', 280.00, 15, 3),
    ('Teclado mecanico', 'Teclado para computadora', 150.00, 12, 4),
    ('Mouse gamer', 'Mouse optico gamer', 90.00, 25, 4);
GO

INSERT INTO Cliente
    (Nombres, Apellidos, Correo, Telefono)
VALUES
    ('Carlos', 'Ramirez', 'carlos@gmail.com', '987654321'),
    ('Maria', 'Lopez', 'maria@gmail.com', '986543210'),
    ('Jose', 'Torres', 'jose@gmail.com', '985432109');
GO

/*
    HASH_DEMO_001 y HASH_DEMO_002 son valores ficticios
    para los datos de prueba; no son contrasenas utilizables.
*/
INSERT INTO Usuario
    (Nombres, Apellidos, Correo, Contrasena, Rol)
VALUES
    ('Alberto', 'Perez', 'alberto@techstore.com',
     'HASH_DEMO_001', 'Administrador'),
    ('Lucia', 'Fernandez', 'lucia@techstore.com',
     'HASH_DEMO_002', 'Vendedor');
GO

INSERT INTO Pedido
    (Total, Estado, IdCliente, IdUsuario)
VALUES
    (2680.00, 'Entregado', 1, 1),
    (640.00, 'Pendiente', 2, 2);
GO

INSERT INTO DetallePedido
    (IdPedido, IdProducto, Cantidad, PrecioUnitario)
VALUES
    (1, 1, 1, 2500.00),
    (1, 5, 2, 90.00),
    (2, 2, 2, 180.00),
    (2, 3, 1, 280.00);
GO

/* =========================================================
   3. CREACION DE INDICES
   ========================================================= */

CREATE INDEX IX_Producto_Nombre
    ON Producto(Nombre);
GO

CREATE INDEX IX_Pedido_Fecha
    ON Pedido(FechaPedido);
GO

CREATE INDEX IX_DetallePedido_IdPedido
    ON DetallePedido(IdPedido);
GO

/* =========================================================
   4. PROCEDIMIENTO ALMACENADO
   Permite consultar todos los pedidos o filtrar por cliente.
   ========================================================= */

CREATE OR ALTER PROCEDURE sp_ConsultarPedidos
    @IdCliente INT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        P.IdPedido,
        C.Nombres AS Cliente,
        PR.Nombre AS Producto,
        DP.Cantidad,
        DP.PrecioUnitario,
        (DP.Cantidad * DP.PrecioUnitario) AS Subtotal,
        P.Total AS TotalPedido,
        P.Estado
    FROM Pedido AS P
    INNER JOIN Cliente AS C
        ON P.IdCliente = C.IdCliente
    INNER JOIN DetallePedido AS DP
        ON P.IdPedido = DP.IdPedido
    INNER JOIN Producto AS PR
        ON DP.IdProducto = PR.IdProducto
    WHERE @IdCliente IS NULL
       OR P.IdCliente = @IdCliente
    ORDER BY P.IdPedido, DP.IdDetalle;
END;
GO

/* =========================================================
   5. COMPROBACIONES
   ========================================================= */

-- Cantidad de registros por tabla.
SELECT 'Categoria' AS Tabla, COUNT(*) AS Registros FROM Categoria
UNION ALL
SELECT 'Producto', COUNT(*) FROM Producto
UNION ALL
SELECT 'Cliente', COUNT(*) FROM Cliente
UNION ALL
SELECT 'Usuario', COUNT(*) FROM Usuario
UNION ALL
SELECT 'Pedido', COUNT(*) FROM Pedido
UNION ALL
SELECT 'DetallePedido', COUNT(*) FROM DetallePedido;
GO

-- Verificacion de las relaciones y los datos de pedidos.
EXEC sp_ConsultarPedidos;
GO

-- Ejemplo de consulta filtrada por cliente.
EXEC sp_ConsultarPedidos @IdCliente = 1;
GO


CREATE TABLE Producto (
    IdProducto INT IDENTITY(1,1) PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Descripcion VARCHAR(200),
    Precio DECIMAL(10,2) NOT NULL,
    Stock INT NOT NULL,
    IdCategoria INT NOT NULL,
    Estado BIT NOT NULL DEFAULT 1,

    CONSTRAINT FK_Producto_Categoria
        FOREIGN KEY (IdCategoria)
        REFERENCES Categoria(IdCategoria),

    CONSTRAINT CK_Producto_Precio
        CHECK (Precio > 0),

    CONSTRAINT CK_Producto_Stock
        CHECK (Stock >= 0)
);