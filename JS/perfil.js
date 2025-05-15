document.addEventListener('DOMContentLoaded', () => {
    const radioButtons = document.querySelectorAll('input[name="radio"]');
    const seccionFavoritas = document.getElementById('seccionFavoritas');
    const seccionPublicadas = document.getElementById('seccionPublicadas');

    // Función para mostrar la sección correcta
    function mostrarSeccion(seleccion) {
        if (seleccion === 'MisFavs') {
            if (seccionFavoritas) seccionFavoritas.classList.add('active-listado');
            if (seccionPublicadas) seccionPublicadas.classList.remove('active-listado');
        } else if (seleccion === 'MisPub') {
            if (seccionFavoritas) seccionFavoritas.classList.remove('active-listado');
            if (seccionPublicadas) seccionPublicadas.classList.add('active-listado');
        }
    }

    // Mostrar la sección inicial basada en el radio 'checked'
    const radioCheckedInicial = document.querySelector('input[name="radio"]:checked');
    if (radioCheckedInicial) {
        mostrarSeccion(radioCheckedInicial.value);
    }

    // Añadir event listeners a los radio buttons
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            mostrarSeccion(this.value);
        });
    });

    // Función para manejar el clic en el botón de favorito
    async function handleFavoriteClick(event) {
        const button = event.target.closest('.btn-favorite'); // Asegura que obtengamos el botón si se hizo clic en el ícono
        if (!button) return; // Si el clic no fue en un botón de favorito, salir

        const fontId = button.dataset.fontid;
        const isCurrentlyFavorite = button.dataset.isfavorite === 'true'; // Estado actual
        const iconElement = button.querySelector('i');
        const fontCard = button.closest('.font-card'); // La tarjeta de la fuente

        try {
            const response = await fetch('BACK/administrarFavs.php?id=' + fontId, {
                method: 'GET', // O POST si lo cambiaste
                headers: {
                    'Content-Type': 'application/json',
                    // 'X-Requested-With': 'XMLHttpRequest' // Buena práctica para que el backend sepa que es AJAX
                }
                // body: JSON.stringify({ id: fontId }) // Si usas POST
            });

            if (!response.ok) {
                const errorText = await response.text(); // Intenta obtener más info del error
                throw new Error(`HTTP error! status: ${response.status}, Response: ${errorText}`);
            }

            const data = await response.json();

            if (data.success) {
                if (data.action === 'unlike') {
                    // Se quitó de favoritos
                    if (iconElement) {
                        iconElement.classList.remove('fa-solid');
                        iconElement.classList.add('fa-regular');
                    }
                    button.dataset.isfavorite = 'false';
                    button.title = 'Añadir a favoritos';

                    // --- LÓGICA PARA OCULTAR LA TARJETA ---
                    // Verifica si la tarjeta está dentro de la sección de "Favoritas"
                    // y si la pestaña "Favoritas" está activa.
                    if (fontCard && seccionFavoritas && seccionFavoritas.contains(fontCard) && seccionFavoritas.classList.contains('active-listado')) {
                        // Ocultar la tarjeta con una animación (opcional) o directamente
                        fontCard.style.transition = 'opacity 0.5s ease, transform 0.5s ease, height 0.5s ease'; // Prepara para animación
                        fontCard.style.opacity = '0';
                        fontCard.style.transform = 'scale(0.9)';
                        // fontCard.style.height = '0px'; // Esto puede ser complicado si el alto no es fijo
                        // fontCard.style.padding = '0';
                        // fontCard.style.margin = '0';
                        // fontCard.style.border = 'none';


                        setTimeout(() => {
                            fontCard.remove(); // Elimina el elemento del DOM después de la animación

                            // Opcional: Verificar si la lista de favoritas está vacía y mostrar mensaje
                            if (seccionFavoritas.querySelectorAll('.font-card').length === 0) {
                                const noFavsMessage = seccionFavoritas.querySelector('p'); // Asume que tienes un <p> para "no hay favoritas"
                                if (noFavsMessage && noFavsMessage.textContent.includes("Aún no has añadido")) {
                                    noFavsMessage.style.display = 'block'; // O crea uno si no existe
                                } else { // Si no hay un p adecuado, crear uno
                                     const p = document.createElement('p');
                                     p.textContent = 'Aún no has añadido ninguna fuente a tus favoritas.';
                                     // Encuentra dónde insertarlo, quizás después del h3
                                     const h3 = seccionFavoritas.querySelector('h3');
                                     if(h3) h3.insertAdjacentElement('afterend', p);
                                     else seccionFavoritas.appendChild(p);
                                }
                            }
                        }, 500); // Tiempo igual a la duración de la transición
                    }

                } else if (data.action === 'like') {
                    // Se añadió a favoritos
                    if (iconElement) {
                        iconElement.classList.remove('fa-regular');
                        iconElement.classList.add('fa-solid');
                    }
                    button.dataset.isfavorite = 'true';
                    button.title = 'Quitar de favoritos';
                    // Nota: Si esta acción de 'like' ocurre en la pestaña "Mis Fuentes Publicadas",
                    // la tarjeta NO debería desaparecer de allí. Solo se actualiza el ícono.
                }
            } else {
                if (data.action === 'redirect_login') {
                    window.location.href = '../DaFont_Log.php?error=not_logged_favorite';
                } else {
                    console.error('Error al actualizar el favorito:', data.message);
                    if (typeof showNotification === 'function') {
                        showNotification(data.message || 'Ocurrió un error al procesar tu solicitud.', 'error');
                    }
                }
            }

        } catch (error) {
            console.error('Error en la solicitud AJAX para favoritos:', error);
            if (typeof showNotification === 'function') {
                showNotification('Error de comunicación al actualizar favoritos.', 'error');
            }
        }
    }

    // Delegación de eventos en los contenedores de las listas de fuentes
    if (seccionFavoritas) {
        seccionFavoritas.addEventListener('click', handleFavoriteClick);
    }
    if (seccionPublicadas) {
        seccionPublicadas.addEventListener('click', handleFavoriteClick);
    }

    // Si tienes botones de favorito en otras partes de la página (ej. DaFont_index.php),
    // necesitarás un selector más general o adjuntar el listener de forma similar.
    // Por ejemplo, para toda la página:
    // document.body.addEventListener('click', handleFavoriteClick);
    // Pero es mejor ser más específico si es posible para evitar conflictos.


   });