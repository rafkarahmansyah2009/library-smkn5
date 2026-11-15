<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Global Session Flash Messages
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: {!! json_encode(session('success')) !!},
                confirmButtonColor: '#1e293b',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl font-semibold px-5 py-2.5'
                }
            });
        @elseif (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                text: {!! json_encode(session('error')) !!},
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Tutup',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl font-semibold px-5 py-2.5'
                }
            });
        @elseif (session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: {!! json_encode(session('warning')) !!},
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl font-semibold px-5 py-2.5'
                }
            });
        @elseif (session('info'))
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: {!! json_encode(session('info')) !!},
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl font-semibold px-5 py-2.5'
                }
            });
        @elseif (session('status'))
            @php
                $statusMessage = match(session('status')) {
                    'profile-updated' => 'Profil berhasil diperbarui.',
                    'password-updated' => 'Kata sandi berhasil diperbarui.',
                    'verification-link-sent' => 'Tautan verifikasi baru telah dikirimkan ke email Anda.',
                    default => session('status'),
                };
            @endphp
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: {!! json_encode($statusMessage) !!},
                confirmButtonColor: '#1e293b',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl font-semibold px-5 py-2.5'
                }
            });
        @endif

        // 2. Centralized Form Confirmation Handler
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('form.confirm-action, form[data-confirm]');
            if (!form) return;

            // Prevent recursion if already confirmed
            if (form.dataset.confirmed === 'true') {
                return;
            }

            e.preventDefault();

            const title = form.dataset.confirmTitle || 'Konfirmasi Tindakan';
            const message = form.dataset.confirmMessage || form.dataset.confirm || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            const icon = form.dataset.confirmIcon || 'question';
            const confirmText = form.dataset.confirmButtonText || (icon === 'warning' ? 'Ya, Hapus!' : (icon === 'question' ? 'Ya, Lanjutkan!' : 'Ya, Lanjutkan!'));
            const cancelText = form.dataset.cancelButtonText || 'Batal';

            // Resolve button color
            let confirmColor = form.dataset.confirmButtonColor;
            if (!confirmColor) {
                if (icon === 'warning') {
                    confirmColor = '#dc2626'; // Destruktif (Merah)
                } else if (icon === 'info') {
                    confirmColor = '#059669'; // Persetujuan / Info (Hijau)
                } else {
                    confirmColor = '#1e293b'; // Default Navy
                }
            }

            Swal.fire({
                title: title,
                text: message,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#64748b',
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl font-semibold px-5 py-2.5',
                    cancelButton: 'rounded-xl font-semibold px-5 py-2.5'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });

        // 3. Centralized Click Confirmation Handler (for non-submit buttons & links)
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('a.confirm-action, a[data-confirm], button.confirm-action:not([type="submit"]), button[data-confirm]:not([type="submit"])');
            if (!trigger) return;

            if (trigger.dataset.confirmed === 'true') {
                return;
            }

            e.preventDefault();

            const title = trigger.dataset.confirmTitle || 'Konfirmasi Tindakan';
            const message = trigger.dataset.confirmMessage || trigger.dataset.confirm || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            const icon = trigger.dataset.confirmIcon || 'question';
            const confirmText = trigger.dataset.confirmButtonText || (icon === 'warning' ? 'Ya, Lanjutkan!' : 'Ya, Lanjutkan!');
            const cancelText = trigger.dataset.cancelButtonText || 'Batal';

            let confirmColor = trigger.dataset.confirmButtonColor;
            if (!confirmColor) {
                if (icon === 'warning') {
                    confirmColor = '#dc2626';
                } else if (icon === 'info') {
                    confirmColor = '#059669';
                } else {
                    confirmColor = '#1e293b';
                }
            }

            Swal.fire({
                title: title,
                text: message,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#64748b',
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl font-semibold px-5 py-2.5',
                    cancelButton: 'rounded-xl font-semibold px-5 py-2.5'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    trigger.dataset.confirmed = 'true';
                    if (trigger.tagName.toLowerCase() === 'a' && trigger.href) {
                        window.location.href = trigger.href;
                    } else if (typeof trigger.onclick === 'function') {
                        trigger.onclick();
                    }
                }
            });
        });
    });

    // 4. Global replacement for native window.alert
    window.alert = function (message) {
        let icon = 'info';
        const lower = String(message || '').toLowerCase();
        if (lower.includes('berhasil') || lower.includes('sukses') || lower.includes('success')) {
            icon = 'success';
        } else if (lower.includes('gagal') || lower.includes('error') || lower.includes('salah')) {
            icon = 'error';
        } else if (lower.includes('peringatan') || lower.includes('habis') || lower.includes('tidak valid') || lower.includes('warning') || lower.includes('perhatian')) {
            icon = 'warning';
        }

        return Swal.fire({
            icon: icon,
            title: icon === 'success' ? 'Berhasil!' : (icon === 'error' ? 'Terjadi Kesalahan' : (icon === 'warning' ? 'Perhatian' : 'Informasi')),
            text: message,
            confirmButtonColor: icon === 'error' ? '#dc2626' : (icon === 'warning' ? '#f59e0b' : '#1e293b'),
            confirmButtonText: 'OK',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl font-semibold px-5 py-2.5'
            }
        });
    };
</script>
