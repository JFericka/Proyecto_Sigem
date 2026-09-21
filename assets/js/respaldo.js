// assets/js/respaldo.js
document.getElementById('form-respaldo').addEventListener('submit', function(e) {
    e.preventDefault(); 

    const form = this;
    const formData = new FormData(form);
    const btn = document.getElementById('btn-respaldo');
    const spinner = document.getElementById('spinner');
    const loadingText = document.getElementById('loading-text');
    const loadingDiv = document.getElementById('loading');
    const mensaje = document.getElementById('mensaje');

    // Activar animación
    btn.disabled = true;
    btn.style.opacity = '0.7';
    loadingDiv.style.display = 'block';
    mensaje.innerHTML = '';

    // Apuntamos directamente a tu controlador
    fetch('../controllers/respaldocontroller.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        // Obtenemos el tipo de contenido que devuelve el controlador PHP
        const contentType = response.headers.get('content-type');
        
        // Si la respuesta es un archivo descargable (octet-stream)
        if (response.ok && contentType && contentType.includes('application/octet-stream')) {
            const blob = await response.blob();
            let filename = 'sigem_respaldo.bak';
            const disposition = response.headers.get('content-disposition');
            
            if (disposition && disposition.indexOf('filename=') !== -1) {
                const matches = /filename="([^"]*)"/.exec(disposition);
                if (matches != null && matches[1]) filename = matches[1];
            }

            // Crear enlace temporal para forzar la descarga en el navegador
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            
            return { status: 'success', msg: 'Respaldo generado y descargado exitosamente.' };
        } else {
            // Si no es un archivo, asumimos que el controlador devolvió un texto de error
            const errorText = await response.text();
            throw new Error(errorText || 'Ocurrió un error inesperado al procesar el respaldo.');
        }
    })
    .then(data => {
        // Finaliza carga con éxito
        loadingDiv.style.display = 'none';
        btn.disabled = false;
        btn.style.opacity = '1';
        mensaje.innerHTML = `<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; font-size: 0.85rem;">
                                <i class="fa-solid fa-check-circle"></i> ${data.msg}
                            </div>`;
        form.reset();
    })
    .catch(error => {
        // Finaliza carga con error
        loadingDiv.style.display = 'none';
        btn.disabled = false;
        btn.style.opacity = '1';
        mensaje.innerHTML = `<div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; font-size: 0.85rem;">
                                <i class="fa-solid fa-triangle-exclamation"></i> ${error.message}
                            </div>`;
    });
});