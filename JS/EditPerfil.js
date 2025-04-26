function previewImage() {
    const fileInput = document.getElementById('imgRuta');
    const img = document.getElementById('imgPerfil');
    if (!fileInput || !img) return;

    const file = fileInput.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        img.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');


    if (error === 'usuer_exists') {
        alert('El nombre de usuario ya existe.');
    } else if (error === 'email_exists') {
        alert('El correo electrónico ya existe.');
    }else if(error === image_upload_error){
        alert('Error al subir la imagen. Por favor, inténtalo de nuevo.');
    }else if(error === 'invalid_request'){
        alert('Error en la solicitud. Por favor, inténtalo de nuevo.');}

    // Elementos del formulario
    const nombre = document.querySelector("input[name='nombre']");
    const apellido = document.querySelector("input[name='apellido']");
    const Pagina = document.querySelector("input[name='Pagina_usuario']");
    // const contraseñacheck = document.querySelector("input[name='contraseña_Check']");
    const fechaNacimientoInput = document.querySelector("input[name='fecha_usuario']");
    const emailInput = document.querySelector("input[name='email_usuario']");
    const form = document.querySelector("form");

    // Validación en tiempo real
    const validarCampo = (campo, regex, mensaje) => {
        if (!regex.test(campo.value)) {
            campo.setCustomValidity(mensaje);
            campo.reportValidity(); // Muestra el mensaje inmediatamente
            return false;
        } else {
            campo.setCustomValidity("");
            return true;
        }
    };

    nombre.addEventListener('input', () => {
        const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        validarCampo(nombreCompletoInput, nombreRegex, "El nombre solo puede contener letras y espacios.");
    });
    apellido.addEventListener('input', () => {
        const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        validarCampo(nombreCompletoInput, nombreRegex, "El nombre solo puede contener letras y espacios.");
    });

  Pagina.addEventListener('input', () => {
      const paginaRegex = /^(https?:\/\/)?(www\.)?[a-zA-Z0-9\-]+\.[a-zA-Z]{2,}([\/\w \.-]*)*\/?$/;
      validarCampo(Pagina, paginaRegex, "Ingrese un link valido. ");
 });

    // contraseñacheck.addEventListener('input', () => {
    //     if (contraseñaInput.value !== contraseñacheck.value) {
    //         contraseñacheck.setCustomValidity("Las contraseñas no coinciden.");
    //         contraseñacheck.reportValidity();
    //     } else {
    //         contraseñacheck.setCustomValidity("");
    //     }
    // });

    fechaNacimientoInput.addEventListener('input', () => {
        if (!fechaNacimientoInput.value) {
            fechaNacimientoInput.setCustomValidity("Por favor, selecciona una fecha.");
            fechaNacimientoInput.reportValidity();
            return;
        }

        const fechaSeleccionada = new Date(fechaNacimientoInput.value);
        const fechaActual = new Date();
        if (fechaSeleccionada > fechaActual) {
            fechaNacimientoInput.setCustomValidity("La fecha de nacimiento no puede ser en el futuro.");
            fechaNacimientoInput.reportValidity();
        } else {
            fechaNacimientoInput.setCustomValidity("");
        }
    });

    emailInput.addEventListener('input', () => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        validarCampo(emailInput, emailRegex, "Por favor, ingresa un correo electrónico válido.");
    });

    // Validación al enviar
    form.addEventListener('submit', (event) => {
        let esValido = true;

        // Forzar validación de todos los campos
        esValido = validarCampo(nombreCompletoInput, /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/, "El nombre solo puede contener letras y espacios.") && esValido;
        esValido = validarCampo(contraseñaInput, /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/, "Contraseña inválida.") && esValido;
        
        if (contraseñaInput.value !== contraseñacheck.value) {
            contraseñacheck.setCustomValidity("Las contraseñas no coinciden.");
            contraseñacheck.reportValidity();
            esValido = false;
        }

        if (!fechaNacimientoInput.value) {
            fechaNacimientoInput.setCustomValidity("Por favor, selecciona una fecha.");
            fechaNacimientoInput.reportValidity();
            esValido = false;
        }

        esValido = validarCampo(emailInput, /^[^\s@]+@[^\s@]+\.[^\s@]+$/, "Correo electrónico inválido.") && esValido;

        if (!esValido) {
            event.preventDefault();
            alert("Por favor, corrige los errores en el formulario.");
        }
    });
});
