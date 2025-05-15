const pswInput = document.querySelector("input[name='psw-change']");
document.addEventListener('DOMContentLoaded', function() {
    togglePasswordVisibility(); // Llama a la función para establecer el estado inicial
const nombreCompletoInput = document.querySelector("input[name='nombre']");
const apellidoInput = document.querySelector("input[name='apellido']");
const PaginaInput = document.querySelector("input[name='Pagina_usuario']");
const contraseñaInput = document.querySelector("input[name='contraseña_usuario']");
const contraseñacheck = document.querySelector("input[name='contraseña_Check']");
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

nombreCompletoInput.addEventListener('input', () => {
    const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
    validarCampo(nombreCompletoInput, nombreRegex, "El nombre solo puede contener letras y espacios.");
});
apellidoInput.addEventListener('input', () => {
    const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
    validarCampo(apellidoInput, nombreRegex, "El apellido solo puede contener letras y espacios.");
}
);
PaginaInput.addEventListener('input', () => {
    const paginaRegex = /^(https?:\/\/)?(www\.)?[a-zA-Z0-9\-]+\.[a-zA-Z]{2,}([\/\w \.-]*)*\/?$/;
    validarCampo(PaginaInput, paginaRegex, "Ingrese un link valido. ");
}   
);

if(pswInput.checked){


contraseñaInput.addEventListener('input', () => {
    const contraseñaRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    validarCampo(contraseñaInput, contraseñaRegex, 
        "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.");
});

contraseñacheck.addEventListener('input', () => {
    if (contraseñaInput.value !== contraseñacheck.value) {
        contraseñacheck.setCustomValidity("Las contraseñas no coinciden.");
        contraseñacheck.reportValidity();
    } else {
        contraseñacheck.setCustomValidity("");
    }
});
}

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
    
    
    if (pswInput.checked){
        esValido = validarCampo(contraseñaInput, /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/, "Contraseña inválida.") && esValido;
    if (contraseñaInput.value !== contraseñacheck.value) {
        contraseñacheck.setCustomValidity("Las contraseñas no coinciden.");
        contraseñacheck.reportValidity();
        esValido = false;
    }
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





function previewImage() {
const fileInput = document.getElementById('imgRuta');
const img = document.getElementById('imgPerfil');
const imgContainer = document.getElementById('img-contenedor');
if (!fileInput || !img) return;

const file = fileInput.files[0];
if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
        img.src = e.target.result;
        img.style.display = 'block';
        imgContainer.style.marginBottom='200px'; // Mostrar el contenedor de la imagen
    };
    reader.readAsDataURL(file);
} else {
    img.style.display = 'none';
}
}

function togglePasswordVisibility(){
const pswCont=document.getElementById('psw-contenedor');
const pswCont2=document.getElementById('psw-contenedor2');

if(pswInput.checked){
    pswCont.style.display='block';
    pswCont2.style.display='block';
    pswCont.required = true;
    pswCont2.required = true;
}else{
    pswCont.style.display='none';
    pswCont.required = false;
    pswCont.value = "";
    pswCont2.style.display='none';
    pswCont2.required = false;
    pswCont2.value = "";
}
}


// function previewImage() {
//     const fileInput = document.getElementById('imgRuta');
//     const img = document.getElementById('imgPerfil');
//     if (!fileInput || !img) return;

//     const file = fileInput.files[0];
//     if (file) {
//         const reader = new FileReader();
//         reader.onload = function (e) {
//             img.src = e.target.result;
//             img.style.display = 'block';
//         };
//         reader.readAsDataURL(file);
//     } else {
//         img.style.display = 'none';
//     }
// }

// document.addEventListener('DOMContentLoaded', () => {
//     const urlParams = new URLSearchParams(window.location.search);
//     const error = urlParams.get('error');


//     if (error === 'usuer_exists') {
//         alert('El nombre de usuario ya existe.');
//     } else if (error === 'email_exists') {
//         alert('El correo electrónico ya existe.');
//     }else if(error === image_upload_error){
//         alert('Error al subir la imagen. Por favor, inténtalo de nuevo.');
//     }else if(error === 'invalid_request'){
//         alert('Error en la solicitud. Por favor, inténtalo de nuevo.');}

//     // Elementos del formulario
//     const nombre = document.querySelector("input[name='nombre']");
//     const apellido = document.querySelector("input[name='apellido']");
//     const Pagina = document.querySelector("input[name='Pagina_usuario']");
//     // const contraseñacheck = document.querySelector("input[name='contraseña_Check']");
//     const fechaNacimientoInput = document.querySelector("input[name='fecha_usuario']");
//     const emailInput = document.querySelector("input[name='email_usuario']");
//     const form = document.querySelector("form");

//     // Validación en tiempo real
//     const validarCampo = (campo, regex, mensaje) => {
//         if (!regex.test(campo.value)) {
//             campo.setCustomValidity(mensaje);
//             campo.reportValidity(); // Muestra el mensaje inmediatamente
//             return false;
//         } else {
//             campo.setCustomValidity("");
//             return true;
//         }
//     };

//     nombre.addEventListener('input', () => {
//         const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
//         validarCampo(nombreCompletoInput, nombreRegex, "El nombre solo puede contener letras y espacios.");
//     });
//     apellido.addEventListener('input', () => {
//         const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
//         validarCampo(nombreCompletoInput, nombreRegex, "El nombre solo puede contener letras y espacios.");
//     });

//   Pagina.addEventListener('input', () => {
//       const paginaRegex = /^(https?:\/\/)?(www\.)?[a-zA-Z0-9\-]+\.[a-zA-Z]{2,}([\/\w \.-]*)*\/?$/;
//       validarCampo(Pagina, paginaRegex, "Ingrese un link valido. ");
//  });

//     // contraseñacheck.addEventListener('input', () => {
//     //     if (contraseñaInput.value !== contraseñacheck.value) {
//     //         contraseñacheck.setCustomValidity("Las contraseñas no coinciden.");
//     //         contraseñacheck.reportValidity();
//     //     } else {
//     //         contraseñacheck.setCustomValidity("");
//     //     }
//     // });

//     fechaNacimientoInput.addEventListener('input', () => {
//         if (!fechaNacimientoInput.value) {
//             fechaNacimientoInput.setCustomValidity("Por favor, selecciona una fecha.");
//             fechaNacimientoInput.reportValidity();
//             return;
//         }

//         const fechaSeleccionada = new Date(fechaNacimientoInput.value);
//         const fechaActual = new Date();
//         if (fechaSeleccionada > fechaActual) {
//             fechaNacimientoInput.setCustomValidity("La fecha de nacimiento no puede ser en el futuro.");
//             fechaNacimientoInput.reportValidity();
//         } else {
//             fechaNacimientoInput.setCustomValidity("");
//         }
//     });

//     emailInput.addEventListener('input', () => {
//         const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
//         validarCampo(emailInput, emailRegex, "Por favor, ingresa un correo electrónico válido.");
//     });

//     // Validación al enviar
//     form.addEventListener('submit', (event) => {
//         let esValido = true;

//         // Forzar validación de todos los campos
//         esValido = validarCampo(nombreCompletoInput, /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/, "El nombre solo puede contener letras y espacios.") && esValido;
//         esValido = validarCampo(contraseñaInput, /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/, "Contraseña inválida.") && esValido;
        
//         if (contraseñaInput.value !== contraseñacheck.value) {
//             contraseñacheck.setCustomValidity("Las contraseñas no coinciden.");
//             contraseñacheck.reportValidity();
//             esValido = false;
//         }

//         if (!fechaNacimientoInput.value) {
//             fechaNacimientoInput.setCustomValidity("Por favor, selecciona una fecha.");
//             fechaNacimientoInput.reportValidity();
//             esValido = false;
//         }

//         esValido = validarCampo(emailInput, /^[^\s@]+@[^\s@]+\.[^\s@]+$/, "Correo electrónico inválido.") && esValido;

//         if (!esValido) {
//             event.preventDefault();
//             alert("Por favor, corrige los errores en el formulario.");
//         }
//     });
// });
