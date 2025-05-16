document.addEventListener("DOMContentLoaded", function() {
    // Determinar si estamos en la página de índice.
    // Puedes hacer esto de varias maneras, por ejemplo, verificando el pathname
    // o si un elemento específico del index existe.
    const isIndexPage = window.location.pathname.endsWith("DaFont_index.php") || window.location.pathname.endsWith("/"); // Ajusta si tu URL del index es diferente

    // Solo ejecutar la lógica de breadcrumb del index si estamos en la página de índice.
    // El breadcrumb de la página de detalles se maneja directamente con PHP en ese archivo.
    if (isIndexPage) {
        const breadcrumbContainer = document.getElementById("breadcrumb");

        function buildBreadcrumbFromURL() {
            if (!breadcrumbContainer) return;

            const urlParams = new URLSearchParams(window.location.search);
            // Empezamos siempre con "Inicio"
            let breadcrumbHTML = '<span><a href="DaFont_index.php">Inicio</a></span>';

            const category = urlParams.get('category');
            const subcategory = urlParams.get('subcategory');
            const searchTerm = urlParams.get('search_term');

            if (category) {
                breadcrumbHTML += `<span><a href="DaFont_index.php?category=${encodeURIComponent(category)}">${decodeURIComponent(category)}</a></span>`;
            }
            // Mostrar subcategoría solo si hay categoría y subcategoría
            if (category && subcategory) {
                breadcrumbHTML += `<span><a href="DaFont_index.php?category=${encodeURIComponent(category)}&subcategory=${encodeURIComponent(subcategory)}">${decodeURIComponent(subcategory)}</a></span>`;
            }
            
            // Si hay un término de búsqueda y no estamos ya mostrando una categoría (para evitar duplicar "niveles")
            if (searchTerm) {
                 // Podrías decidir si mostrar la búsqueda junto con la categoría o si la búsqueda "resetea" el breadcrumb de categoría
                 // Por ahora, lo añadimos independientemente.
                breadcrumbHTML += `<span>Búsqueda: "${decodeURIComponent(searchTerm)}"</span>`;
            }
            
            breadcrumbContainer.innerHTML = breadcrumbHTML;
        }

        // Construir el breadcrumb al cargar la página de índice
        buildBreadcrumbFromURL();
    }

    // Los listeners para .submenu a y a.category-btn que prevenían la navegación
    // ya deberían estar comentados o eliminados de tu versión anterior para permitir que los enlaces funcionen.
    // Si no lo están, asegúrate de que e.preventDefault() no se llame para esos enlaces.
});