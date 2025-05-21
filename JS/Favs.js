function showInfo(message) { // type puede ser 'success', 'error', 'info'
   const infoArea=document.getElementById('info-area');
    if (!infoArea) {
        console.error('Elemento #info-area no encontrado.');
        // Fallback si el div no existe en la página actual
        alert(`[${type.toUpperCase()}] ${message}`);
        return;
    }


    // Crea el contenido del mensaje y el botón de cerrar
    infoArea.innerHTML = `
        <span>${message}</span>
        <button class="close-btn" aria-label="Cerrar notificación">&times;</button>
    `;

    // Muestra el área
    infoArea.style.display = 'flex';

    // Funcionalidad del botón de cerrar
    const closeButton = infoArea.querySelector('.close-btn');
    closeButton.onclick = () => {
        infoArea.style.display = 'none';
        infoArea.innerHTML = ''; 
    };
  
    setTimeout(() => {
        if (infoArea.style.display !== 'none') {
             infoArea.style.display = 'none';
             infoArea.innerHTML = '';
        }
    }, 3000); 
    
}

document.addEventListener('DOMContentLoaded', () => {
    const likeButtons = document.querySelectorAll('.btn-favorite');

    likeButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const FontId = this.dataset.fontid;
         

            try {
                const response = await fetch('BACK/administrarFavs.php?id=' + FontId, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                
if (data.success) {
    const iconElement = this.querySelector('i'); // Selecciona el elemento <i> dentro del botón

    if (data.action === 'like') {
        // this.classList.add('liked'); // Puedes añadir una clase al botón si quieres estilizarlo diferente
        iconElement.classList.remove('fa-regular');
        iconElement.classList.add('fa-solid');
        this.setAttribute('data-isfavorite', 'true'); // Actualiza el estado
        this.title = 'Quitar de favoritos'; // Actualiza el tooltip
        showInfo('Fuente agregada a Favoritos');
    } else if (data.action === 'unlike') {
        // this.classList.remove('liked');
        iconElement.classList.remove('fa-solid');
        iconElement.classList.add('fa-regular');
        this.setAttribute('data-isfavorite', 'false'); // Actualiza el estado
        this.title = 'Añadir a favoritos'; // Actualiza el tooltip
        showInfo('Fuente eliminada de Favoritos');
    }
    
} else {

    if (data.action === 'redirect_login') { // Manejar la redirección
        window.location.href = 'DaFont_Log.php?error=not_logged'; // O la ruta que necesites
    } else {
        console.error('Error al actualizar el like:', data.message);
        // Opcionalmente, mostrar un mensaje de error al usuario usando tu función showNotification
        if (typeof showNotification === 'function') {
            showNotification(data.message || 'Ocurrió un error.', 'error');
        }
    }
}

            } catch (error) {
                console.error('Error al enviar la petición de like:', error);
            }
        });
    });
});