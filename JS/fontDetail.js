document.addEventListener("DOMContentLoaded", function() {
    const textInputDetail = document.getElementById("text-input-detail");
    const fontSizeRangeDetail = document.getElementById("font-size-range-detail");
    const fontSizeValueDetail = document.getElementById("font-size-value-detail");
    const fontPreviewDetailed = document.getElementById("font-preview-detailed");

    function updateFontPreviewDetailed() {
        if (!fontPreviewDetailed || !textInputDetail || !fontSizeRangeDetail) return;
        const newText = textInputDetail.value;
        const newSize = fontSizeRangeDetail.value + "px";
        fontPreviewDetailed.textContent = newText;
  fontPreviewDetailed.style.fontSize = newSize;
        if(fontSizeValueDetail) fontSizeValueDetail.textContent = newSize;
    }

    if (textInputDetail) textInputDetail.addEventListener("input", updateFontPreviewDetailed);
    if (fontSizeRangeDetail) fontSizeRangeDetail.addEventListener("input", updateFontPreviewDetailed);

    if (fontPreviewDetailed) updateFontPreviewDetailed();

    const downloadBtnDetail = document.querySelector('.font-detail-container .download-btn');
    if (downloadBtnDetail) {
        downloadBtnDetail.addEventListener('click', function() {
            const fontId = this.dataset.fontId;
            const fontName = "<?php echo ($font && isset($font['fontName'])) ? htmlspecialchars(addslashes($font['fontName'])) : 'font'; ?>";
            
            console.log("Download clicked for font ID:", fontId, "Name:", fontName);
            const content = `Este es un archivo de ejemplo para la fuente: ${fontName}\nID: ${fontId}\n\nLorem ipsum dolor sit amet...`;
            const fileName = `${fontName.replace(/[^a-z0-9]/gi, '_')}_example.txt`;
            const blob = new Blob([content], { type: "text/plain" });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        });
    }

       const interactiveStarsContainer = document.querySelector('.rating-interactive');
    const mensajeCalificacion = document.getElementById('mensaje-calificacion');
    const starsDisplayContainer = document.querySelector('.stars-display'); // Para promedio general
    const ratingAverageText = starsDisplayContainer ? starsDisplayContainer.querySelector('.rating-average') : null;


    if (interactiveStarsContainer) {
        const fontId = interactiveStarsContainer.dataset.fontId;
        const allInteractiveStars = interactiveStarsContainer.querySelectorAll('.interactive-star');
        let currentRating = parseInt(interactiveStarsContainer.dataset.currentRating) || 0;

        // Función para pintar estrellas (hover o selected)
        function paintInteractiveStars(ratingToPaint, isSelectedState) {
            allInteractiveStars.forEach(star => {
                const starValue = parseInt(star.dataset.value);
                if (isSelectedState) { // Aplicando estado 'selected' (calificación fija)
                    if (starValue <= ratingToPaint) {
                        star.classList.add('selected');
                    } else {
                        star.classList.remove('selected');
                    }
                    star.classList.remove('hover'); // Limpiar hover al fijar 'selected'
                } else { // Aplicando estado 'hover' (temporal al pasar el mouse)
                    if (starValue <= ratingToPaint) {
                        star.classList.add('hover');
                    } else {
                        star.classList.remove('hover');
                    }
                }
            });
        }

        // Cargar calificación inicial del usuario y pintar estrellas 'selected'
        if (currentRating > 0) {
            paintInteractiveStars(currentRating, true); // true para estado 'selected'
        }
  

        allInteractiveStars.forEach(star => {
            star.addEventListener('mouseover', function() {
                paintInteractiveStars(parseInt(this.dataset.value), false); // false para estado hover
            });

            star.addEventListener('mouseout', function() {
                // Limpiar todas las clases 'hover'
                allInteractiveStars.forEach(s => s.classList.remove('hover'));
                // Restaurar el estado 'selected' basado en la calificación actual guardada
                paintInteractiveStars(currentRating, true);
            });
        });

        interactiveStarsContainer.addEventListener('click', async function(event) {
            if (event.target.classList.contains('interactive-star')) {
                const estrellasSeleccionadas = parseInt(event.target.dataset.value);
                ocultarMensaje('mensaje-calificacion');

                try {
                    const formData = new FormData();
                    formData.append('font_id', fontId);
                    formData.append('estrellas', estrellasSeleccionadas);
                    const response = await fetch('BACK/guardar_calificacion.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({ message: `Error HTTP ${response.status}: ${response.statusText}` }));
                        throw new Error(errorData.message || `Error HTTP: ${response.status}`);
                    }

                    const data = await response.json();
                    mostrarMensaje('mensaje-calificacion', data.message, data.success);

                    if (data.success) {
                        currentRating = estrellasSeleccionadas; // Actualizar la calificación actual en JS
                        interactiveStarsContainer.dataset.currentRating = currentRating; // Actualizar el atributo data

                        // 1. Actualizar visualmente las estrellas interactivas ('selected')
                        paintInteractiveStars(currentRating, true);

    

                        // 2. Actualizar el promedio general y número de votos
                        if (starsDisplayContainer && ratingAverageText && data.nuevoPromedio !== undefined && data.nuevoTotalVotos !== undefined) {
                            const averageStars = starsDisplayContainer.querySelectorAll('.star'); // Estrellas del promedio general
                            const roundedAverage = Math.round(data.nuevoPromedio);
                            averageStars.forEach((star, index) => {
                                if ((index + 1) <= roundedAverage) {
                                    star.classList.add('filled');
                                } else {
                                    star.classList.remove('filled');
                                }
                            });
                            ratingAverageText.textContent = `(${data.nuevoPromedio} de ${data.nuevoTotalVotos} votos)`;
                        }
                    }
                } catch (error) {
                    console.error('Error al guardar calificación:', error);
                    mostrarMensaje('mensaje-calificacion', error.message || 'Ocurrió un error al enviar tu calificación.', false);
                }
            }
        });
    }


});
async function guardarComentario(event) {
    event.preventDefault();
    ocultarMensaje('mensaje-comentario');
    const form = document.getElementById('form-comentario');
    const comentarioTextarea = form.querySelector('textarea[name="comentario"]');
    const fontInput = form.querySelector('input[name="font_id_comentario"]');

    const comentario = comentarioTextarea.value.trim();

    if (!comentario) {
         mostrarMensaje('mensaje-comentario', 'El comentario no puede estar vacío.', false);
        return;
    }
     if (!fontInput || !fontInput.value) {
         mostrarMensaje('mensaje-comentario', 'Error: ID de fuente no encontrado.', false);
         return;
    }

    const fontId = fontInput.value;

    try {
        const response = await fetch('BACK/guardar_comentario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'font_id': fontId, // Nombres coinciden con PHP
                'comentario': comentario
            })
        });

         if (!response.ok) {
           throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();

        mostrarMensaje('mensaje-comentario', data.message, data.success);

        if (data.success) {
            // Añadir dinámicamente el nuevo comentario a la lista
            const listaComentarios = document.getElementById('lista-comentarios');
            const primerComentario = listaComentarios.querySelector('.comentario-item, p'); // Busca item existente o el mensaje "sé el primero"

            const nuevoComentarioDiv = document.createElement('div');
            nuevoComentarioDiv.className = 'comentario-item';
            // Usar el nombre de usuario de la sesión actual
            const nombreUsuarioActual = "<?= $user_name ?>"; // Obtener nombre desde PHP
            nuevoComentarioDiv.innerHTML = `
                <strong>${nombreUsuarioActual}</strong>
                <span> (Ahora):</span>
                <p>${comentario.replace(/\n/g, '<br>')}</p> `;

            // Si había mensaje "sé el primero", quitarlo
            if (primerComentario && primerComentario.tagName === 'P') {
                listaComentarios.innerHTML = ''; // Limpiar lista
            }

            // Añadir al principio de la lista
            listaComentarios.insertBefore(nuevoComentarioDiv, listaComentarios.firstChild);

            // Limpiar el textarea
            comentarioTextarea.value = '';
        }

    } catch (error) {
        console.error('Error al guardar comentario:', error);
        mostrarMensaje('mensaje-comentario', 'Ocurrió un error al publicar el comentario.', false);
    }
}


function mostrarMensaje(elementoId, mensaje, esExito = true) {
    const el = document.getElementById(elementoId);
    if (!el) return;
    el.textContent = mensaje;
    el.className = 'mensaje-ajax ' + (esExito ? 'success' : 'error');
    el.style.display = 'block';
    // Opcional: Ocultar después de unos segundos
     setTimeout(() => { el.style.display = 'none'; }, 3000);
}

function ocultarMensaje(elementoId) {
     const el = document.getElementById(elementoId);
    if (el) el.style.display = 'none';
}


