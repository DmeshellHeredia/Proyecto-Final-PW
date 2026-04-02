/**
 * Librería Áurea — app.js
 */

// ---- Normalización de texto (sin acentos) ----
function normalizar(t) {
    return String(t || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}
function escapeHtml(t) {
    return String(t || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ============================================================
//  1. Buscador en tiempo real con resaltado
// ============================================================
function inicializarBuscador(campoBusquedaId, tablaBodyId, conteoId) {
    var campo  = document.getElementById(campoBusquedaId);
    var tbody  = document.getElementById(tablaBodyId);
    var conteo = document.getElementById(conteoId);
    if (!campo || !tbody) return;

    campo.addEventListener('input', function () {
        var termino  = normalizar(this.value.trim());
        var filas    = Array.from(tbody.querySelectorAll('tr:not(.fila-vacia)'));
        var visibles = 0;

        filas.forEach(function (fila) {
            fila.querySelectorAll('[data-original]').forEach(function (el) {
                el.innerHTML = el.getAttribute('data-original');
                el.removeAttribute('data-original');
            });

            if (termino === '') {
                fila.style.display = '';
                visibles++;
                return;
            }

            if (normalizar(fila.textContent).includes(termino)) {
                fila.style.display = '';
                visibles++;

                fila.querySelectorAll('td').forEach(function (td) {
                    if (td.querySelector('a,button,.badge-tipo,.avatar-autor')) return;

                    var texto = td.textContent;
                    if (!normalizar(texto).includes(termino)) return;

                    td.setAttribute('data-original', td.innerHTML);

                    var res = '', pos = 0;
                    while (pos < texto.length) {
                        var found = normalizar(texto.substring(pos)).indexOf(termino);
                        if (found === -1) {
                            res += escapeHtml(texto.substring(pos));
                            break;
                        }
                        res += escapeHtml(texto.substring(pos, pos + found));
                        res += '<mark class="resalto">' +
                               escapeHtml(texto.substring(pos + found, pos + found + termino.length)) +
                               '</mark>';
                        pos += found + termino.length;
                    }
                    td.innerHTML = res;
                });
            } else {
                fila.style.display = 'none';
            }
        });

        var fv = tbody.querySelector('.fila-vacia-js');
        if (visibles === 0 && termino !== '') {
            if (!fv) {
                fv = document.createElement('tr');
                fv.className = 'fila-vacia fila-vacia-js';
                var td = document.createElement('td');
                td.colSpan = 20;
                fv.appendChild(td);
                tbody.appendChild(fv);
            }
            fv.querySelector('td').textContent = 'No se encontraron resultados para "' + campo.value + '".';
            fv.style.display = '';
        } else if (fv) {
            fv.style.display = 'none';
        }

        if (conteo) {
            conteo.textContent = termino
                ? 'Mostrando ' + visibles + ' de ' + filas.length + ' registros'
                : 'Total: ' + filas.length + ' registros';
        }
    });
}

// ============================================================
//  2. Validación del formulario de contacto
// ============================================================
function inicializarFormContacto() {
    var form = document.getElementById('formContacto');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var valido = true;

        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
            el.removeAttribute('aria-invalid');
        });

        form.querySelectorAll('.invalid-feedback').forEach(function (el) {
            el.textContent = '';
        });

        var nombre = document.getElementById('nombre');
        if (nombre && !nombre.value.trim()) {
            marcarInvalido(nombre, 'El nombre es obligatorio.');
            valido = false;
        }

        var correo = document.getElementById('correo');
        if (correo) {
            if (!correo.value.trim()) {
                marcarInvalido(correo, 'El correo electrónico es obligatorio.');
                valido = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo.value.trim())) {
                marcarInvalido(correo, 'Ingresa un correo electrónico válido.');
                valido = false;
            }
        }

        var asunto = document.getElementById('asunto');
        if (asunto && !asunto.value.trim()) {
            marcarInvalido(asunto, 'El asunto es obligatorio.');
            valido = false;
        }

        var comentario = document.getElementById('comentario');
        if (comentario) {
            if (!comentario.value.trim()) {
                marcarInvalido(comentario, 'El comentario no puede estar vacío.');
                valido = false;
            } else if (comentario.value.trim().length < 10) {
                marcarInvalido(comentario, 'El comentario debe tener al menos 10 caracteres.');
                valido = false;
            }
        }

        if (!valido) {
            e.preventDefault();
            var p = form.querySelector('.is-invalid');
            if (p) {
                p.scrollIntoView({ behavior: 'smooth', block: 'center' });
                p.focus();
            }
        }
    });
}

function marcarInvalido(campo, msg) {
    campo.classList.add('is-invalid');
    campo.setAttribute('aria-invalid', 'true');

    var fb = campo.nextElementSibling;
    if (!fb || !fb.classList.contains('invalid-feedback')) {
        fb = document.createElement('div');
        fb.className = 'invalid-feedback';
        campo.parentNode.insertBefore(fb, campo.nextSibling);
    }
    fb.textContent = msg;
}

// ============================================================
//  3. Contador de caracteres
// ============================================================
function inicializarContadorCaracteres() {
    var ta = document.getElementById('comentario');
    var cc = document.getElementById('charCount');
    if (!ta || !cc) return;

    ta.addEventListener('input', function () {
        cc.textContent = this.value.length + ' / 2000 caracteres';
    });
}

// ============================================================
//  4. Animación de números con IntersectionObserver
// ============================================================
function inicializarAnimacionNumeros() {
    var els = document.querySelectorAll('.stat-numero[data-objetivo]');
    if (!els.length || !('IntersectionObserver' in window)) return;

    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;

            var el = e.target;
            var obj = parseInt(el.dataset.objetivo, 10) || 0;
            var pasos = 40;
            var inter = 1200 / pasos;
            var actual = 0;

            var timer = setInterval(function () {
                actual += Math.ceil(obj / pasos);
                if (actual >= obj) {
                    actual = obj;
                    clearInterval(timer);
                }
                el.textContent = actual.toLocaleString('es-DO');
            }, inter);

            obs.unobserve(el);
        });
    }, { threshold: 0.3 });

    els.forEach(function (el) {
        obs.observe(el);
    });
}

// ============================================================
//  5. Animaciones reveal-up
// ============================================================
function inicializarRevealUp() {
    var els = document.querySelectorAll('.reveal-up');
    if (!els.length) return;

    if (!('IntersectionObserver' in window)) {
        els.forEach(function (el) { el.classList.add('visible'); });
        return;
    }

    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    var grupos = {};
    els.forEach(function (el) {
        var k = el.parentElement ? el.parentElement.className : 'root';
        if (!grupos[k]) grupos[k] = [];
        grupos[k].push(el);
    });

    Object.values(grupos).forEach(function (g) {
        g.forEach(function (el, i) {
            el.style.transitionDelay = (i * 0.08) + 's';
            obs.observe(el);
        });
    });
}

// ============================================================
//  6. Auto-ocultar alertas (JS puro, no depende de Bootstrap API)
// ============================================================
function autoOcultarAlertas() {
    document.querySelectorAll('.alert').forEach(function (alerta) {
        var btn = alerta.querySelector('.btn-close, [data-bs-dismiss="alert"]');

        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                cerrarAlerta(alerta);
            });
        }

        if (alerta.classList.contains('alert-dismissible')) {
            setTimeout(function () { cerrarAlerta(alerta); }, 5000);
        }
    });
}

function cerrarAlerta(alerta) {
    if (!alerta.parentNode) return;

    alerta.style.transition = 'opacity .35s ease, max-height .35s ease';
    alerta.style.opacity    = '0';
    alerta.style.overflow   = 'hidden';
    alerta.style.maxHeight  = alerta.offsetHeight + 'px';

    setTimeout(function () {
        alerta.style.maxHeight = '0';
        alerta.style.marginBottom = '0';
    }, 10);

    setTimeout(function () {
        if (alerta.parentNode) alerta.parentNode.removeChild(alerta);
    }, 400);
}

// ============================================================
//  7. Collapse manual — fallback si Bootstrap JS no carga
// ============================================================
function inicializarCollapseManual() {
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (btn) {
        var targetSel = btn.getAttribute('data-bs-target');
        if (!targetSel) return;

        var panel = document.querySelector(targetSel);
        if (!panel) return;

        btn.addEventListener('click', function (e) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                return;
            }

            e.preventDefault();
            var open = panel.classList.contains('show');

            if (open) {
                panel.classList.remove('show');
                btn.setAttribute('aria-expanded', 'false');
            } else {
                panel.classList.add('show');
                btn.setAttribute('aria-expanded', 'true');
            }
        });

        if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
            panel.addEventListener('shown.bs.collapse', function () {
                btn.setAttribute('aria-expanded', 'true');
            });
            panel.addEventListener('hidden.bs.collapse', function () {
                btn.setAttribute('aria-expanded', 'false');
            });
        }
    });
}

// ============================================================
//  8. Modo oscuro con localStorage separado
// ============================================================
function aplicarTemaPorClave(clave, btnId, iconoId) {
    var btn   = document.getElementById(btnId);
    var icono = document.getElementById(iconoId);
    if (!btn) return;

    var html = document.documentElement;

    function aplicarTema(tema) {
        html.setAttribute('data-theme', tema);
        localStorage.setItem(clave, tema);

        if (icono) {
            icono.className = tema === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }

        btn.setAttribute('aria-label', tema === 'dark' ? 'Activar modo claro' : 'Activar modo oscuro');
        btn.title = tema === 'dark' ? 'Modo claro' : 'Modo oscuro';
    }

    aplicarTema(localStorage.getItem(clave) || 'light');

    btn.addEventListener('click', function () {
        aplicarTema(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
}

function inicializarModoOscuro() {
    aplicarTemaPorClave('tema_publico', 'btnTema', 'iconoTema');
    aplicarTemaPorClave('tema_admin', 'btnTemaAdmin', 'iconoTemaAdmin');
}

// ============================================================
//  9. Carousel hero — Bootstrap o fallback manual
// ============================================================
function inicializarHeroCarousel() {
    var carousel = document.getElementById('heroCarousel');
    if (!carousel) return;

    if (typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
        var instancia = bootstrap.Carousel.getOrCreateInstance(carousel, {
            interval: 4500,
            ride: 'carousel',
            pause: false,
            touch: true,
            wrap: true
        });
        instancia.cycle();
        return;
    }

    var items = Array.from(carousel.querySelectorAll('.carousel-item'));
    var prev  = carousel.querySelector('.carousel-control-prev');
    var next  = carousel.querySelector('.carousel-control-next');
    var inds  = Array.from(carousel.querySelectorAll('.carousel-indicators button'));
    if (!items.length) return;

    var actual = items.findIndex(function (item) {
        return item.classList.contains('active');
    });
    if (actual < 0) actual = 0;

    function mostrar(i) {
        items.forEach(function (item, idx) {
            item.classList.toggle('active', idx === i);
        });

        inds.forEach(function (btn, idx) {
            btn.classList.toggle('active', idx === i);
            if (idx === i) {
                btn.setAttribute('aria-current', 'true');
            } else {
                btn.removeAttribute('aria-current');
            }
        });

        actual = i;
    }

    function siguiente() {
        mostrar((actual + 1) % items.length);
    }

    function anterior() {
        mostrar((actual - 1 + items.length) % items.length);
    }

    if (next) {
        next.addEventListener('click', function (e) {
            e.preventDefault();
            siguiente();
        });
    }

    if (prev) {
        prev.addEventListener('click', function (e) {
            e.preventDefault();
            anterior();
        });
    }

    inds.forEach(function (btn, idx) {
        btn.addEventListener('click', function () {
            mostrar(idx);
        });
    });

    var timer = setInterval(siguiente, 4500);

    carousel.addEventListener('mouseenter', function () {
        clearInterval(timer);
    });

    carousel.addEventListener('mouseleave', function () {
        timer = setInterval(siguiente, 4500);
    });
}

// ============================================================
//  10. Modal logout — fallback manual
// ============================================================
function inicializarModalLogoutManual() {
    var modal = document.getElementById('modalCerrarSesion');
    if (!modal) return;

    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        return;
    }

    var trigger = document.querySelector('[data-bs-target="#modalCerrarSesion"]');
    if (!trigger) return;

    var cerrarBtns = modal.querySelectorAll('[data-bs-dismiss="modal"]');
    var backdrop = null;

    function abrir(e) {
        if (e) e.preventDefault();

        modal.style.display = 'block';
        modal.classList.add('show');
        modal.removeAttribute('aria-hidden');
        modal.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';

        backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }

    function cerrar() {
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';

        if (backdrop && backdrop.parentNode) {
            backdrop.parentNode.removeChild(backdrop);
            backdrop = null;
        }
    }

    trigger.addEventListener('click', abrir);

    cerrarBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            cerrar();
        });
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) cerrar();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            cerrar();
        }
    });
}

// ============================================================
//  INICIALIZACIÓN
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    inicializarBuscador('campoBusqueda',      'tbodyLibros',  'conteoLibros');
    inicializarBuscador('campoBusquedaAutor', 'tbodyAutores', 'conteoAutores');
    inicializarFormContacto();
    inicializarContadorCaracteres();
    inicializarAnimacionNumeros();
    inicializarRevealUp();
    autoOcultarAlertas();
    inicializarCollapseManual();
    inicializarModoOscuro();
    inicializarHeroCarousel();
    inicializarModalLogoutManual();
});