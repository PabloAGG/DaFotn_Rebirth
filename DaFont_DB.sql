Create database Dafont;
use Dafont;
alter table Usuario modify contraseña nvarchar(255);
select*from Usuario;

CREATE TABLE Usuario(
idUsuario int auto_increment primary key,
usuario nvarchar(50),
correo nvarchar(255),
contraseña nvarchar(50),
nombres nvarchar(50),
apellidos nvarchar(50),
natal date,
imgPath nvarchar(255) NULL,
pagina nvarchar(255) NULL
);

CREATE TABLE Fonts(
idFont int auto_increment primary key,
fontName nvarchar(50),
fontAutor int,
fontStyle nvarchar(50),
descargas int,
estrellas int,
 FOREIGN KEY (fontAutor) REFERENCES Usuario (idUsuario)
);

CREATE TABLE FontOwner(
idFont int ,
idUsuario int ,
fecha datetime DEFAULT current_timestamp(),
 FOREIGN KEY (idFont) REFERENCES Fonts (idFont),
  FOREIGN KEY (idUsuario) REFERENCES Usuario (idUsuario),
  primary key(idFont,idUsuario)
);

CREATE TABLE FavFonts(
idFont int ,
idUsuario int ,
 FOREIGN KEY (idFont) REFERENCES Fonts (idFont),
  FOREIGN KEY (idUsuario) REFERENCES Usuario (idUsuario),
    primary key(idFont,idUsuario)
);

CREATE TABLE calificaciones(
idCalf int auto_increment primary key,
idUsuario int,
idFont int, 
estrellas int,
 FOREIGN KEY (idFont) REFERENCES Fonts (idFont),
  FOREIGN KEY (idUsuario) REFERENCES Usuario (idUsuario)
);