document.addEventListener("DOMContentLoaded", function() {

     const botonAbrir = document.getElementById("btn-filtros");
 const botonCerrar = document.querySelector(".hideMenu");
    const aside = document.querySelector("aside");
  
     // Mostrar aside
     botonAbrir.addEventListener("click", () => {
      aside.classList.add("active");
     });
  
     // Ocultar aside
     botonCerrar.addEventListener("click", () => {
       aside.classList.remove("active");
     });
  
    //  Cerrar al hacer clic fuera del aside (opcional)
     document.addEventListener("click", function (e) {
       if (
         aside.classList.contains("active") &&
         !aside.contains(e.target) &&
         !botonAbrir.contains(e.target)
       ) {
         aside.classList.remove("active");
       }
     });

       const downloadButton = document.querySelector('.download-btn'); 
       downloadButton.addEventListener("click", function () {
         const content = "Esto es un archivo de Fuente ejemplo Por DaFont\n" +
         "lorem ipsum dolor sit amet, consectetur adipiscing elit.\n" +
         "Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n";
         const fileName = "Font.txt";
    
          //Crear un blob con el contenido
         const blob = new Blob([content], { type: "text/plain" });
    
         // Crear un enlace de descarga
         const link = document.createElement("a");
         link.href = URL.createObjectURL(blob);
         link.download = fileName;
    
         // Simular clic en el enlace
         link.click();
    
        //  Liberar la URL del objeto
         URL.revokeObjectURL(link.href);
     });
});

 const fontSizeRange = document.getElementById('font-size-range');

 function adjustSliderMax() {
     if (window.matchMedia("(max-width: 768px)").matches) {
         // Pantalla pequeña (móvil) → max = 70
         fontSizeRange.max = 70;
        //  Si el valor actual es mayor que 70, lo ajustamos
         if (fontSizeRange.value > 70) {
             fontSizeRange.value = 70;
         }
     } else {
        //  Pantalla grande (desktop) → max = 100
         fontSizeRange.max = 100;
     }
 }

 // Ejecutamos al cargar y al cambiar el tamaño de la pantalla
 window.addEventListener('load', adjustSliderMax);
 window.addEventListener('resize', adjustSliderMax);