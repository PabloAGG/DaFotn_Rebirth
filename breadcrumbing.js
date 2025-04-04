document.addEventListener("DOMContentLoaded", function() {
    const breadcrumb = document.getElementById("breadcrumb");
  
    // Función para actualizar el breadcrumb
    function updateBreadcrumb(...items) {
      breadcrumb.innerHTML = items.map(item => 
        `<span> > <a href="${item.link || '#'}">${item.name}</a></span>`
      ).join("");
    }
  
    // Ejemplo 1: Cuando se selecciona una categoría (ej: Fantasía > Épico)
    document.querySelectorAll(".submenu a").forEach(link => {
      link.addEventListener("click", function(e) {
        e.preventDefault();
        updateBreadcrumb(
          { name: "Inicio", link: "DaFont_index.html" },
          { name: this.parentElement.parentElement.previousElementSibling.textContent, link: "DaFont_index.html" }, // Categoría padre (ej: Fantasía)
          { name: this.textContent, link: "DaFont_index.html" } // Subcategoría (ej: Épico)
        );
      });
    });
  
    // // Ejemplo 2: Cuando se selecciona una fuente
    // document.querySelectorAll(".font-name").forEach(font => {
    //   font.addEventListener("click", function(e) {
    //     e.preventDefault();
    //     updateBreadcrumb(
    //       { name: "Inicio", link: "DaFont_index.html" },
    //       { name: "Fantasía", link: "#" }, // Categoría (simulada)
    //       { name: "Épico", link: "#" },    // Subcategoría (simulada)
    //       { name: this.textContent, link: "#" } // Nombre de la fuente
    //     );
    //     window.location.href = "font-details.html"; // Redirige a la página de detalles
    //   });
    //});
  });