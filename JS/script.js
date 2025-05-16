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

    const dropdownButtons = document.querySelectorAll(".dropdown > button.category-btn");
    const dropdowns = document.querySelectorAll(".dropdown"); // Selecciona los contenedores li.dropdown

    dropdownButtons.forEach(button => {
        button.addEventListener("click", function(event) {
            event.stopPropagation(); 
            const currentDropdown = this.parentElement; 

            // Cierra todos los demás submenús antes de abrir/cerrar el actual
            dropdowns.forEach(otherDropdown => {
                if (otherDropdown !== currentDropdown) {
                    otherDropdown.classList.remove("active");
                }
            });

            currentDropdown.classList.toggle("active");
        });
    });

    // Cerrar submenús si se hace clic fuera de ellos
    document.addEventListener("click", function(event) {
        let clickedInsideDropdown = false;
        dropdowns.forEach(dropdown => {
            if (dropdown.contains(event.target)) {
                clickedInsideDropdown = true;
            }
        });

        // Si no se hizo clic dentro de un dropdown, cierra todos
        if (!clickedInsideDropdown) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove("active");
            });
        }
    });
    const toggleBtn = document.getElementById("dkmode");
    const body = document.body;
    const navImg = document.getElementById("navImg");

    // Detectar si ya hay un modo guardado
    if (localStorage.getItem("modo-oscuro") === "desactivado") {
        body.classList.add("light-mode");
        navImg.src = "Dafont1-Light1.png";
    }

    toggleBtn.addEventListener("click", () => {
        body.classList.toggle("light-mode");

        if (body.classList.contains("light-mode")) {
            navImg.src = "Dafont1-Light1.png";
            localStorage.setItem("modo-oscuro", "desactivado");
        } else {
            navImg.src = "Dafont1-Dark1.png";
            localStorage.setItem("modo-oscuro", "activado");
        }
    });
    
    const searchButtonInForm = document.querySelector('.search-container-form .search-button'); // El botón de submit con la lupa
    const searchContainerDiv = document.querySelector('.search-container-form .search-container'); // El div que contiene input y botón
    const searchBarInputEl = document.querySelector('.search-container-form .search-bar'); // El campo de texto para buscar

    if (searchButtonInForm && searchContainerDiv && searchBarInputEl) {
        searchButtonInForm.addEventListener('click', function(event) {
            // Si el contenedor de la barra de búsqueda (searchContainerDiv) NO está activo (visible)
            if (!searchContainerDiv.classList.contains('active')) {
                // Prevenimos la acción por defecto del botón (que sería enviar el formulario)
                event.preventDefault();
                // Hacemos visible el contenedor de la búsqueda
                searchContainerDiv.classList.add('active');
                // Ponemos el foco en el campo de texto para que el usuario pueda escribir inmediatamente
                searchBarInputEl.focus();
            }
            // Si el contenedor YA está 'activo' (visible), no hacemos nada con event.preventDefault().
            // El botón es type="submit", así que el navegador se encargará de enviar el formulario.
            // Ya no usamos classList.toggle() aquí, lo que evita que la barra se oculte.
        });

        // Opcional: Si quieres que la barra de búsqueda se oculte si el usuario hace clic fuera de ella
        // Y el campo de búsqueda está vacío.
        document.addEventListener('click', function(event) {
            if (searchContainerDiv.classList.contains('active') &&
                !searchContainerDiv.contains(event.target) && /* El clic fue fuera del searchContainerDiv */
                event.target !== searchButtonInForm && /* El clic no fue en el propio botón de búsqueda */
                !searchButtonInForm.contains(event.target) /* El clic no fue en un hijo del botón (ej. el ícono) */
                ) {
                // Ocultar solo si el campo de búsqueda está vacío, como una conveniencia
                if (searchBarInputEl.value.trim() === '') {
                    searchContainerDiv.classList.remove('active');
                }
            }
        });
    }
    
    
    // document.querySelector('.search-button').addEventListener('click', function() {
    //     const searchContainer = document.querySelector('.search-container');
    //     searchContainer.classList.toggle('active');
    //   });

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

