document.addEventListener("DOMContentLoaded", function() {

    // const botonAbrir = document.getElementById("btn-filtros");
    // const botonCerrar = document.querySelector(".hideMenu");
    // const aside = document.querySelector("aside");
  
    // // Mostrar aside
    // botonAbrir.addEventListener("click", () => {
    //   aside.classList.add("active");
    // });
  
    // // Ocultar aside
    // botonCerrar.addEventListener("click", () => {
    //   aside.classList.remove("active");
    // });
  
    // // Cerrar al hacer clic fuera del aside (opcional)
    // document.addEventListener("click", function (e) {
    //   if (
    //     aside.classList.contains("active") &&
    //     !aside.contains(e.target) &&
    //     !botonAbrir.contains(e.target)
    //   ) {
    //     aside.classList.remove("active");
    //   }
    // });



    const menuHamburguesa = document.querySelector(".menu-hamburguesa");
    const navLinks = document.querySelector(".nav-links");
    const closeMenu = document.getElementById("closeMenu");

    menuHamburguesa.addEventListener("click", function() {
        navLinks.classList.toggle("active");
    });
// Abrir menú
menuHamburguesa.addEventListener("click", function() {
    navMenu.classList.add("active");
});

// Cerrar menú con el botón ❌
closeMenu.addEventListener("click", function() {
    navMenu.classList.remove("active");
});

// Cerrar menú al hacer clic fuera de él
document.addEventListener("click", function(event) {
    if (!navMenu.contains(event.target) && !menuHamburguesa.contains(event.target)) {
        navMenu.classList.remove("active");
    }
});

    // Hacer que los submenús se desplieguen en móviles al tocar
    document.querySelectorAll(".dropdown > button").forEach(button => {
        button.addEventListener("click", function(event) {
            event.stopPropagation();
            this.parentElement.classList.toggle("active");
        });
    });
    const botonModo = document.getElementById("dkmode");
    const body = document.body;

    // Verificar si hay una preferencia guardada en localStorage
    if (localStorage.getItem("modo-claro") === "activado") {
        body.classList.add("light-mode");
    }

    // Cambiar de modo al hacer clic en el botón
    botonModo.addEventListener("click", function() {
        body.classList.toggle("light-mode");

        // Guardar la preferencia en localStorage
        if (body.classList.contains("light-mode")) {
            localStorage.setItem("modo-claro", "activado");
        } else {
            localStorage.setItem("modo-claro", "desactivado");
        }
    });
    document.querySelector('.search-button').addEventListener('click', function() {
        const searchContainer = document.querySelector('.search-container');
        searchContainer.classList.toggle('active');
      });

    //   const downloadButton = document.querySelector('.download-btn'); 
    //   downloadButton.addEventListener("click", function () {
    //     const content = "Esto es un archivo de Fuente ejemplo Por DaFont\n" +
    //     "lorem ipsum dolor sit amet, consectetur adipiscing elit.\n" +
    //     "Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n";
    //     const fileName = "Font.txt";
    
    //     // Crear un blob con el contenido
    //     const blob = new Blob([content], { type: "text/plain" });
    
    //     // Crear un enlace de descarga
    //     const link = document.createElement("a");
    //     link.href = URL.createObjectURL(blob);
    //     link.download = fileName;
    
    //     // Simular clic en el enlace
    //     link.click();
    
    //     // Liberar la URL del objeto
    //     URL.revokeObjectURL(link.href);
    // });
});

// const fontSizeRange = document.getElementById('font-size-range');

// function adjustSliderMax() {
//     if (window.matchMedia("(max-width: 768px)").matches) {
//         // Pantalla pequeña (móvil) → max = 70
//         fontSizeRange.max = 70;
//         // Si el valor actual es mayor que 70, lo ajustamos
//         if (fontSizeRange.value > 70) {
//             fontSizeRange.value = 70;
//         }
//     } else {
//         // Pantalla grande (desktop) → max = 100
//         fontSizeRange.max = 100;
//     }
// }

// // Ejecutamos al cargar y al cambiar el tamaño de la pantalla
// window.addEventListener('load', adjustSliderMax);
// window.addEventListener('resize', adjustSliderMax);