// assets/js/acceso.js
// Muestra una ventanita "Acceso no autorizado" al lado del botón cuando el usuario no tiene permiso.
(function () {
    let burbuja = null, temporizador = null;

    function ocultar() {
        if (burbuja) { burbuja.remove(); burbuja = null; }
        clearTimeout(temporizador);
    }

    function mostrar(elemento) {
        ocultar();
        const r = elemento.getBoundingClientRect();
        burbuja = document.createElement('div');
        burbuja.innerHTML = '<i class="fa-solid fa-lock"></i> Acceso no autorizado';
        burbuja.style.cssText = 'position:fixed;z-index:9999;background:#C53030;color:#fff;font:700 12px Inter,sans-serif;' +
            'padding:8px 12px;border-radius:8px;box-shadow:0 4px 14px rgba(0,0,0,.25);white-space:nowrap;pointer-events:none;';
        document.body.appendChild(burbuja);

        const ancho = burbuja.offsetWidth, alto = burbuja.offsetHeight;
        let left = r.right + 8;                                   // al lado derecho del botón
        if (left + ancho > window.innerWidth - 6) { left = r.left - ancho - 8; }   // si no cabe, al lado izquierdo
        if (left < 6) { left = Math.max(6, r.left + (r.width - ancho) / 2); }      // último recurso: centrado
        let top = r.top + (r.height - alto) / 2;
        top = Math.min(Math.max(6, top), window.innerHeight - alto - 6);
        burbuja.style.left = left + 'px';
        burbuja.style.top = top + 'px';

        temporizador = setTimeout(ocultar, 2200);
    }

    function manejar(e) {
        const el = e.target.closest('[data-permiso="0"]');
        if (!el) return;
        e.preventDefault();
        e.stopPropagation();
        mostrar(el);
    }

    document.addEventListener('click', manejar, true);
    document.addEventListener('dblclick', manejar, true);
})();
