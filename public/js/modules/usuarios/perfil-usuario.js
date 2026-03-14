/**
 * perfil-usuario.js — Lógica de la página de perfil del usuario autenticado
 */

// ─── Vista previa de imagen ──────────────────────────────────────────────────

document.getElementById('imagen').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        Swal.fire({ icon: 'error', title: 'Archivo muy grande', text: 'El archivo no debe superar los 2 MB.' });
        e.target.value = '';
        return;
    }

    const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowed.includes(file.type)) {
        Swal.fire({ icon: 'error', title: 'Formato no válido', text: 'Solo se permiten JPG, PNG, GIF y WEBP.' });
        e.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('preview-image').src = ev.target.result;
        document.getElementById('sidebar-avatar').src = ev.target.result;
    };
    reader.readAsDataURL(file);

    const label = e.target.nextElementSibling;
    if (label) label.textContent = file.name;
});

// ─── Cambiar contraseña (AJAX) ───────────────────────────────────────────────

document.getElementById('formCambiarPassword').addEventListener('submit', function (e) {
    e.preventDefault();

    const nuevaClave     = document.getElementById('nueva_clave').value;
    const confirmarClave = document.getElementById('confirmar_nueva_clave').value;

    if (nuevaClave !== confirmarClave) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Las contraseñas no coinciden.' });
        return;
    }

    const btn = document.getElementById('btnCambiarPassword');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    fetch(`${baseUrl}controllers/usuarios/ajax_cambiar_clave.php`, {
        method: 'POST',
        body: new FormData(this)
    })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-key mr-1"></i> Cambiar contraseña';

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Contraseña actualizada',
                    text: 'Se cerrará la sesión.',
                    allowOutsideClick: false,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = `${baseUrl}controllers/auth/logout.php`;
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-key mr-1"></i> Cambiar contraseña';
            Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error al procesar la solicitud.' });
        });
});
