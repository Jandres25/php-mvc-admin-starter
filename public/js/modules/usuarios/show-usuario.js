/**
 * Módulo JS para la vista de detalle de usuario
 */

// Restaurar la última pestaña activa
var lastTab = localStorage.getItem('lastUserDetailTab');
if (lastTab && $('a[href="' + lastTab + '"]').length) {
    $('a[href="' + lastTab + '"]').tab('show');
}

// Guardar la pestaña activa al cambiar
$('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
    localStorage.setItem('lastUserDetailTab', $(e.target).attr('href'));
});

// Mostrar imagen ampliada al hacer clic
$('.profile-user-img').on('click', function () {
    Swal.fire({
        imageUrl: $(this).attr('src'),
        imageAlt: 'Imagen de perfil',
        confirmButtonText: 'Cerrar',
        customClass: { image: 'img-fluid' }
    });
});
