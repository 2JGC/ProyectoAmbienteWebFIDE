// ==========================================================
// MotoRent Costa Rica - Validaciones del lado del cliente (ES6)
// Complementan, sin sustituir, la validación del servidor (PHP).
// ==========================================================

document.addEventListener('DOMContentLoaded', () => {
    // Bootstrap: activa los estilos de validación nativos del navegador
    document.querySelectorAll('.needs-validation').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            if (!form.checkValidity()) {
                evento.preventDefault();
                evento.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Validación específica: confirmación de contraseña en el registro
    const formRegistro = document.getElementById('formRegistro');
    if (formRegistro) {
        const password = document.getElementById('password');
        const password2 = document.getElementById('password2');

        const validarCoincidencia = () => {
            if (password.value !== password2.value) {
                password2.setCustomValidity('Las contraseñas no coinciden');
            } else {
                password2.setCustomValidity('');
            }
        };

        password.addEventListener('input', validarCoincidencia);
        password2.addEventListener('input', validarCoincidencia);
    }
});
