/**
 * profile-user.js — Logic for the authenticated user's profile page
 */

// ─── Image preview ───────────────────────────────────────────────────────────

document.getElementById('image').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        Swal.fire({ icon: 'error', title: 'File too large', text: 'The file must not exceed 2 MB.' });
        e.target.value = '';
        return;
    }

    const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowed.includes(file.type)) {
        Swal.fire({ icon: 'error', title: 'Invalid format', text: 'Only JPG, PNG, GIF, and WEBP are allowed.' });
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

// ─── Change password (AJAX) ──────────────────────────────────────────────────

document.getElementById('formChangePassword').addEventListener('submit', function (e) {
    e.preventDefault();

    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword !== confirmPassword) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Passwords do not match.' });
        return;
    }

    const btn = document.getElementById('btnChangePassword');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    fetch(`${baseUrl}users/change-password`, {
        method: 'POST',
        body: new FormData(this)
    })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-key mr-1"></i> Change password';

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Password updated',
                    text: 'The session will be closed.',
                    allowOutsideClick: false,
                    confirmButtonText: 'Accept'
                }).then(() => {
                    window.location.href = `${baseUrl}logout`;
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-key mr-1"></i> Change password';
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while processing the request.' });
        });
});
