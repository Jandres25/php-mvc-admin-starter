/**
 * show-user.js - User detail page module
 */

// Restore last active tab
var lastTab = localStorage.getItem('lastUserDetailTab');
if (lastTab && $('a[href="' + lastTab + '"]').length) {
    $('a[href="' + lastTab + '"]').tab('show');
}

// Save active tab on change
$('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
    localStorage.setItem('lastUserDetailTab', $(e.target).attr('href'));
});

// Show enlarged image on click
$('.profile-user-img').on('click', function () {
    Swal.fire({
        imageUrl: $(this).attr('src'),
        imageAlt: 'Profile image',
        confirmButtonText: 'Close',
        customClass: { image: 'img-fluid' }
    });
});
