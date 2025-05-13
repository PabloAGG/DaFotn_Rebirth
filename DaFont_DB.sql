Create database Dafont;
use Dafont;

CREATE TABLE Usuario(
    idUsuario int auto_increment primary key,
    usuario nvarchar(50) UNIQUE,
    correo nvarchar(255) UNIQUE,
    contraseña nvarchar(255),
    nombres nvarchar(50),
    apellidos nvarchar(50),
    natal date,
    imgPath nvarchar(255) NULL, -- Ruta a la imagen de avatar del usuario
    pagina nvarchar(255) NULL,
    fechaRegistro datetime DEFAULT current_timestamp()
);
CREATE TABLE Categorias (
    idCategoria int auto_increment primary key,
    nombreCategoria nvarchar(50) UNIQUE NOT NULL
);
INSERT INTO Categorias (nombreCategoria) VALUES
('Fantasia'),
('Tecno'),
('Gotico'),
('Basico'),
('Script'),
('Display');
CREATE TABLE Fonts(
    idFont int auto_increment primary key,
    fontName nvarchar(50) NOT NULL, -- Nombre de la fuente a mostrar
    fontFamilyCSS nvarchar(100) NOT NULL, -- Nombre exacto para usar en 'font-family' CSS (ej: 'Henny Penny', 'Audiowide')
    fontAutor int NULL, -- FK a Usuario
    fontStyleFallback nvarchar(50), -- Fallback CSS genérico (ej: 'cursive', 'sans-serif')
    descargas int DEFAULT 0,
    licenciaDescripcion TEXT, -- Descripción textual de la licencia (ej: "Gratis para uso personal")
    fechaSubida datetime DEFAULT current_timestamp(),
    FOREIGN KEY (fontAutor) REFERENCES Usuario (idUsuario) ON DELETE SET NULL
);
INSERT INTO Fonts (fontName, fontFamilyCSS, fontAutor, fontStyleFallback, descargas, licenciaDescripcion) VALUES
('Henny Penny', 'Henny Penny', 1, 'system-ui', 23746, 'Gratis para uso personal'),
('Iansui', 'Iansui', 1, 'cursive', 15200, 'Gratis para uso personal'),
('Audiowide', 'Audiowide', 1, 'sans-serif', 18950, 'SIL Open Font License'),
('UnifrakturMaguntia', 'UnifrakturMaguntia', 1, 'cursive', 9870, 'Dominio Público'),
('Times New Roman', 'Times New Roman', NULL, 'serif', 50000, 'Fuente del sistema'),
('Meddon', 'Meddon', 1, 'cursive', 12340, 'Gratis para uso personal'),
('Eater', 'Eater', 1, 'serif', 8760, 'Apache License 2.0'),
('Creepster', 'Creepster', 1, 'system-ui', 11230, 'Gratis para uso personal');

CREATE TABLE FavFonts(
    idFont int,
    idUsuario int,
    fechaAgregado datetime DEFAULT current_timestamp(),
    FOREIGN KEY (idFont) REFERENCES Fonts (idFont) ON DELETE CASCADE,
    FOREIGN KEY (idUsuario) REFERENCES Usuario (idUsuario) ON DELETE CASCADE,
    PRIMARY KEY(idFont, idUsuario)
);

CREATE TABLE calificaciones(
    idCalf int auto_increment primary key,
    idUsuario int,
    idFont int,
    estrellas int CHECK (estrellas >= 1 AND estrellas <= 5),
    fechaCalificacion datetime DEFAULT current_timestamp(),
    FOREIGN KEY (idFont) REFERENCES Fonts (idFont) ON DELETE CASCADE,
    FOREIGN KEY (idUsuario) REFERENCES Usuario (idUsuario) ON DELETE CASCADE,
    UNIQUE KEY `idx_usuario_font_calificacion` (idUsuario, idFont)
);

CREATE TABLE FontCategorias (
    idFont int,
    idCategoria int,
    PRIMARY KEY (idFont, idCategoria),
    FOREIGN KEY (idFont) REFERENCES Fonts (idFont) ON DELETE CASCADE,
    FOREIGN KEY (idCategoria) REFERENCES Categorias (idCategoria) ON DELETE CASCADE
);

INSERT INTO FontCategorias Values (1,1),(2,2),(3,3),(4,4),(5,5),(6,6),(7,3),(8,6);