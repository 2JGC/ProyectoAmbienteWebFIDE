// ==========================================================
// MotoRent Costa Rica - JavaScript general del sitio
// ==========================================================

document.addEventListener('DOMContentLoaded', () => {
    // Cierra automáticamente las alertas flash después de 5 segundos
    document.querySelectorAll('.alert').forEach((alerta) => {
        setTimeout(() => {
            const instancia = bootstrap.Alert.getOrCreateInstance(alerta);
            instancia.close();
        }, 5000);
    });
});
