// assets/js/efecto.js
// Módulo abierto dentro del Dashboard (marco embebido):
//  - oculta su barra superior (la del Dashboard no se mueve) y ocupa todo el espacio, con desplazamiento interno;
//  - el botón "volver" le pide al Dashboard cerrarlo con el efecto humo;
//  - al cambiar de pantalla dentro del módulo (listado -> formulario, guardar, etc.) la pantalla sale y entra con el mismo
//    efecto, pero más rápido, para evitar el parpadeo de la recarga.
(function () {
    if (window.self === window.top) return;   // abierto directamente: funciona como siempre

    const FLAG = 'sigemInterno';
    let interno = false;
    try { interno = sessionStorage.getItem(FLAG) === '1'; sessionStorage.removeItem(FLAG); } catch (e) {}

    const estilo = document.createElement('style');
    estilo.textContent =
        '.navbar { display: none !important; }' +
        '.page-wrapper { padding: 10px !important; align-items: stretch !important; justify-content: stretch !important; min-height: 0 !important; overflow: hidden !important; }' +
        '.main-container { max-width: none !important; width: 100% !important; height: calc(100vh - 20px) !important; min-height: 0 !important; flex: 1 1 auto; }';
    document.head.appendChild(estilo);

    // Si llega por un cambio interno, la pantalla empieza invisible para que no se vea el parpadeo
    const oculto = document.createElement('style');
    if (interno) {
        oculto.textContent = '.main-container { opacity: 0; }';
        document.head.appendChild(oculto);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!interno) return;   // la primera apertura la anima el Dashboard
        const c = document.querySelector('.main-container');
        if (!c) { oculto.remove(); return; }
        const anim = c.animate([
            { opacity: 0, transform: 'scale(0.96)', filter: 'blur(8px)' },
            { opacity: 1, transform: 'none', filter: 'blur(0)' }
        ], { duration: 450, easing: 'cubic-bezier(.22, .61, .36, 1)', fill: 'both' });
        anim.onfinish = function () { oculto.remove(); anim.cancel(); };
    });

    // Sale la pantalla actual con el efecto (rápido) y luego ejecuta la acción de navegar
    function salir(accion) {
        const c = document.querySelector('.main-container');
        try { sessionStorage.setItem(FLAG, '1'); } catch (e) {}
        if (!c) { accion(); return; }
        let hecho = false;
        function ir() { if (hecho) return; hecho = true; accion(); }
        const anim = c.animate([
            { opacity: 1, transform: 'none', filter: 'blur(0)' },
            { opacity: 0, transform: 'scale(0.96)', filter: 'blur(8px)' }
        ], { duration: 300, easing: 'ease-in', fill: 'forwards' });
        anim.onfinish = ir;
        setTimeout(ir, 700);   // por si la animación no corre
    }

    window.sigemEnviar = function (form) {
        salir(function () { HTMLFormElement.prototype.submit.call(form); });
    };

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented) return;
        const a = e.target.closest('a[href]');
        if (!a) return;
        const href = a.getAttribute('href');

        if (/(^|\/)Dashboard\.php/.test(href)) {          // volver: lo cierra el Dashboard
            e.preventDefault();
            window.parent.postMessage({ sigem: 'cerrar' }, window.location.origin);
            return;
        }
        if (!href || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href) || a.hasAttribute('download') ||
            (a.target && a.target !== '_self') || e.ctrlKey || e.metaKey || e.shiftKey) return;

        e.preventDefault();
        salir(function () { window.location.href = a.href; });
    });

    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;
        const f = e.target;
        if (f.target && f.target !== '_self') return;
        e.preventDefault();
        salir(function () { HTMLFormElement.prototype.submit.call(f); });
    });
})();
