document.addEventListener('DOMContentLoaded', function () {
    const currentPath = window.location.pathname;
    document.querySelectorAll('.app-sidebar .nav-link').forEach(function (link) {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.replace(/\/$/, ''))) {
            link.classList.add('active');
        }
    });

    const fotoInput = document.getElementById('fotografiaInput');
    const fotoPreview = document.getElementById('fotoPreview');
    const fotoPlaceholder = document.getElementById('fotoPreviewPlaceholder');

    if (fotoInput && fotoPreview) {
        fotoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    fotoPreview.src = e.target.result;
                    fotoPreview.hidden = false;
                    if (fotoPlaceholder) {
                        fotoPlaceholder.hidden = true;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    const photoModal = document.getElementById('photoModal');
    const photoModalImage = document.getElementById('photoModalImage');

    if (photoModal && photoModalImage && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(photoModal);

        document.querySelectorAll('.photo-thumb-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                photoModalImage.src = this.dataset.photoSrc;
                photoModalImage.alt = this.dataset.photoTitle || 'Fotografía del elemento';
                modal.show();
            });
        });

        photoModal.addEventListener('hidden.bs.modal', function () {
            photoModalImage.src = '';
        });
    }

});
