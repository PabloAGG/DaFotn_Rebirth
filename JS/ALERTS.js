document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');

    // Función para limpiar la URL (opcional, pero recomendada)
    function cleanUrlParams() {
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
    }

    if (error) { // Solo procesa si hay un error
        if (typeof showNotification === 'function') {
            switch (error) {
                case 'username_exists':
                    showNotification('El nombre de usuario ya existe.', 'error');
                    break;
                case 'email_exists':
                    showNotification('El correo electrónico ya existe.', 'error');
                    break;
                case 'user_not_updated':
                    showNotification('El usuario no se ha podido actualizar.', 'error');
                    break;
                case 'image_upload_error':
                    showNotification('Error al subir la imagen.', 'error');
                    break;
                case 'invalid_request':
                    showNotification('Petición inválida.', 'error');
                    break;
              
                case 'password': 
                    showNotification('La contraseña es incorrecta.', 'error');
                    break;
                case 'user_not_found': 
                    showNotification('El nombre de usuario o correo electrónico no existe.', 'error');
                    break;
                case 'user_not_created': 
                    showNotification('El usuario no se pudo crear. Por favor, intenta nuevamente.', 'error');
                    break;
                      case 'not_logged':
            showNotification('Por favor, inicia sesión o crea una cuenta para continuar.', 'error');
            break;
                default:
                    // Opcional: manejar un error desconocido o no hacer nada
                    // console.warn('Error desconocido en URL:', error);
                    break;
            }
        } else {
            // Fallback a alert si showNotification no está definida
            console.warn("Función showNotification no encontrada, usando alert como fallback.");
            switch (error) {
                case 'username_exists':
                    alert('El nombre de usuario ya existe.');
                    break;
                case 'email_exists':
                    alert('El correo electrónico ya existe.');
                    break;
                      case 'user_not_updated':
                    alert('El usuario no se ha podido actualizar.');
                    break;
                case 'image_upload_error':
                    alert('Error al subir la imagen.');
                    break;
                case 'invalid_request':
                    alert('Petición inválida.');
                    break;
              
                case 'password': 
                    alert('La contraseña es incorrecta.');
                    break;
                case 'user_not_found': 
                    alert('El nombre de usuario o correo electrónico no existe.');
                    break;
                case 'user_not_created': 
                    alert('El usuario no se pudo crear. Por favor, intenta nuevamente.');
                    break;
           case 'not_logged':
            alert('Por favor, inicia sesión o crea una cuenta para continuar.');
            break;
                default:
                    break;
            }
        }
        cleanUrlParams(); // Limpia la URL después de mostrar el mensaje de error
    }

    if (success) { // Solo procesa si hay un mensaje de éxito
        if (typeof showNotification === 'function') {
            switch (success) {
                case 'user_updated':
                    showNotification('Perfil actualizado correctamente.', 'success');
                    break;
          
                case 'user_created':
                    showNotification('Usuario creado correctamente. Por favor, inicia sesión.', 'success');
                    break;
                default:
                 
                    break;
            }
        } else {
            // Fallback a alert si showNotification no está definida
            console.warn("Función showNotification no encontrada, usando alert como fallback.");
            switch (success) {
                case 'user_updated':
                    alert('Perfil actualizado correctamente.');
                    break;
                // ... y así sucesivamente para todos los casos de éxito
                default:
                    break;
            }
        }
        cleanUrlParams(); // Limpia la URL después de mostrar el mensaje de éxito
    }
});


function showNotification(message, type = 'info') { // type puede ser 'success', 'error', 'info'
    const notificationArea = document.getElementById('notification-area');
    if (!notificationArea) {
        console.error('Elemento #notification-area no encontrado.');
        // Fallback si el div no existe en la página actual
        alert(`[${type.toUpperCase()}] ${message}`);
        return;
    }

    // Limpia clases anteriores y añade la nueva clase de tipo
    notificationArea.className = 'notification-area'; // Resetea
    notificationArea.classList.add(type); // Añade 'success', 'error' o 'info'

    // Crea el contenido del mensaje y el botón de cerrar
    notificationArea.innerHTML = `
        <span>${message}</span>
        <button class="close-btn" aria-label="Cerrar notificación">&times;</button>
    `;

    // Muestra el área
    notificationArea.style.display = 'flex';

    // Funcionalidad del botón de cerrar
    const closeButton = notificationArea.querySelector('.close-btn');
    closeButton.onclick = () => {
        notificationArea.style.display = 'none';
        notificationArea.innerHTML = ''; // Limpia el contenido
    };

    // Opcional: Ocultar automáticamente después de un tiempo
  
    setTimeout(() => {
        if (notificationArea.style.display !== 'none') {
             notificationArea.style.display = 'none';
             notificationArea.innerHTML = '';
        }
    }, 5000); // Ocultar después de 7 segundos
    
}