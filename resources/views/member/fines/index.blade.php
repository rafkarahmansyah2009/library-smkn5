<x-layouts.member title="Denda Saya">
    <div class="space-y-8">
        <!-- Header Banner -->
        <div class="bg-surface-container-low rounded-3xl p-6 sm:p-10 border border-outline-variant/40 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary-container/15 text-secondary font-label-caps text-label-caps font-bold uppercase tracking-wider mb-2">
                    <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                    Status Denda Keterlambatan
                </div>
                <h1 class="font-display-lg text-display-lg text-primary font-bold">Denda Keterlambatan Saya</h1>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-xl mt-1">
                    Lihat rincian denda keterlambatan buku peminjaman Anda dan bayar secara online dengan cepat melalui Midtrans Snap.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('member.borrowings.current') }}" class="px-5 py-3 bg-surface-container hover:bg-surface-container-high text-on-surface rounded-xl font-body-sm text-body-sm font-semibold transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">auto_stories</span>
                    Peminjaman Aktif
                </a>
            </div>
        </div>

        <!-- Summary Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant/40 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-error-container flex items-center justify-center text-error">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">pending_actions</span>
                </div>
                <div>
                    <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider block">Total Belum Lunas</span>
                    <p class="text-2xl font-bold text-error">
                        Rp {{ number_format($totalUnpaid, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant/40 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-secondary-container/15 flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
                <div>
                    <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider block">Total Sudah Dibayar</span>
                    <p class="text-2xl font-bold text-secondary">
                        Rp {{ number_format($totalPaid, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant/40 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-surface-container flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-2xl">credit_card</span>
                </div>
                <div>
                    <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider block">Metode Pembayaran</span>
                    <p class="text-sm font-bold text-primary">
                        Midtrans Snap / Tunai Kasir
                    </p>
                </div>
            </div>
        </div>

        <!-- Fines List -->
        <div class="space-y-4">
            @forelse ($fines as $fine)
                <div class="bg-surface-container-lowest rounded-2xl p-6 border {{ $fine->isPaid() ? 'border-outline-variant/40' : 'border-error/40 shadow-error/5' }} shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                        <!-- Book & Transaction Info -->
                        <div class="flex items-start gap-4 flex-1">
                            @if ($fine->borrowing?->book?->cover_image)
                                <img src="{{ $fine->borrowing->book->cover_image }}" alt="{{ $fine->borrowing->book->title }}" class="w-16 h-24 object-cover rounded-xl shadow-sm border border-outline-variant/40">
                            @else
                                <div class="w-16 h-24 bg-surface-container rounded-xl flex items-center justify-center text-on-surface-variant border border-outline-variant/40">
                                    <span class="material-symbols-outlined text-2xl">book</span>
                                </div>
                            @endif

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-mono font-bold text-secondary">{{ $fine->borrowing?->transaction_code ?? '-' }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-semibold">
                                        {{ $fine->borrowing?->book?->category?->name ?? 'Koleksi' }}
                                    </span>
                                    @if ($fine->isPaid())
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-secondary-container/20 text-secondary uppercase">
                                            Lunas
                                        </span>
                                    @else
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-error-container text-error uppercase">
                                            Belum Lunas
                                        </span>
                                    @endif
                                </div>

                                <h2 class="font-title-sm text-title-sm font-bold text-primary">
                                    @if ($fine->borrowing?->book)
                                        <a href="{{ route('member.books.show', $fine->borrowing->book) }}" class="hover:text-secondary transition-colors">
                                            {{ $fine->borrowing->book->title }}
                                        </a>
                                    @else
                                        Buku Perpustakaan
                                    @endif
                                </h2>

                                <p class="text-xs text-on-surface-variant">
                                    Penulis: {{ $fine->borrowing?->book?->author ?? '-' }} &bull;
                                    Jatuh Tempo: {{ $fine->borrowing?->due_date ? $fine->borrowing->due_date->format('d/m/Y') : '-' }}
                                </p>

                                @if ($fine->notes)
                                    <p class="text-xs text-on-surface-variant italic mt-1">
                                        Catatan: {{ $fine->notes }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Fine Details & Action Button -->
                        <div class="flex flex-wrap lg:flex-nowrap items-center gap-6 w-full lg:w-auto justify-between lg:justify-end border-t lg:border-t-0 pt-4 lg:pt-0 border-outline-variant/30">
                            <!-- Keterlambatan -->
                            <div class="text-left lg:text-right">
                                <span class="text-xs text-on-surface-variant block">Keterlambatan</span>
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-error-container/40 text-error mt-0.5">
                                    {{ $fine->days_overdue }} Hari
                                </span>
                            </div>

                            <!-- Jumlah Denda -->
                            <div class="text-left lg:text-right">
                                <span class="text-xs text-on-surface-variant block">Jumlah Denda</span>
                                <span class="text-xl font-bold block {{ $fine->isPaid() ? 'text-secondary' : 'text-error' }}">
                                    {{ $fine->formatted_amount }}
                                </span>
                            </div>

                            <!-- Payment Status / Button -->
                            <div class="text-left lg:text-right min-w-[170px]">
                                @if ($fine->isPaid())
                                    <div class="space-y-1">
                                        <div class="flex items-center lg:justify-end gap-1.5 text-secondary font-semibold text-xs">
                                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                            Sudah Dibayar
                                        </div>
                                        <span class="text-[11px] text-on-surface-variant block">
                                            {{ ($fine->paid_at ?? $fine->payment_date) ? ($fine->paid_at ?? $fine->payment_date)->format('d M Y H:i') : '-' }}
                                        </span>
                                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-surface-container inline-block text-on-surface-variant">
                                            Metode: {{ $fine->payment_method === 'midtrans' ? 'Midtrans' : 'Tunai Kasir' }}
                                        </span>
                                    </div>
                                @else
                                    <button
                                        type="button"
                                        id="btn-pay-{{ $fine->id }}"
                                        onclick="payFine({{ $fine->id }}, '{{ $fine->formatted_amount }}')"
                                        class="w-full sm:w-auto px-5 py-2.5 bg-secondary text-on-secondary hover:bg-secondary/90 rounded-xl font-body-sm text-body-sm font-semibold shadow-md transition-all flex items-center justify-center gap-2 group cursor-pointer"
                                    >
                                        <span class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">credit_card</span>
                                        Bayar Sekarang
                                    </button>
                                    <span class="text-[11px] text-on-surface-variant block mt-1">
                                        Snap Sandbox / QRIS / VA
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-surface-container-lowest rounded-3xl p-12 text-center border border-outline-variant/40 space-y-4">
                    <span class="material-symbols-outlined text-6xl text-secondary" style="font-variation-settings: 'FILL' 1;">task_alt</span>
                    <h2 class="font-title-sm text-title-sm font-bold text-primary">Tidak Ada Tagihan Denda</h2>
                    <p class="text-body-sm text-on-surface-variant max-w-md mx-auto">
                        Hebat! Anda tidak memiliki catatan denda keterlambatan buku. Tetap rawat buku dan kembalikan tepat waktu ya.
                    </p>
                    <a href="{{ route('member.borrowings.current') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-on-primary hover:bg-primary/90 rounded-xl font-body-sm text-body-sm font-semibold shadow-md transition-all">
                        <span class="material-symbols-outlined text-[18px]">auto_stories</span>
                        Lihat Peminjaman Aktif
                    </a>
                </div>
            @endforelse
        </div>

        @if ($fines->hasPages())
            <div class="mt-6">
                {{ $fines->links() }}
            </div>
        @endif
    </div>

    <!-- Midtrans Snap JS -->
    <script
        src="{{ config('midtrans.snap_url', 'https://app.sandbox.midtrans.com/snap/snap.js') }}"
        data-client-key="{{ config('midtrans.client_key') }}"
    ></script>

    <!-- Payment Script -->
    <script>
        async function payFine(fineId, formattedAmount) {
            const btn = document.getElementById('btn-pay-' + fineId);
            const originalContent = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Menyiapkan...
            `;

            try {
                const response = await fetch(`{{ url('/member/denda') }}/${fineId}/pay`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Gagal memproses token pembayaran Midtrans.');
                }

                if (!window.snap) {
                    throw new Error('Midtrans Snap JS tidak berhasil dimuat. Periksa koneksi internet Anda.');
                }

                window.snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil!',
                            text: 'Pembayaran denda sebesar ' + formattedAmount + ' telah berhasil diverifikasi.',
                            confirmButtonColor: '#0058be',
                            confirmButtonText: 'Selesai'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    onPending: function(result) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Menunggu Pembayaran',
                            text: 'Silakan selesaikan pembayaran Anda sesuai instruksi pembayaran Midtrans.',
                            confirmButtonColor: '#0058be',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    onError: function(result) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pembayaran Gagal',
                            text: 'Terjadi kesalahan saat memproses transaksi di Midtrans. Silakan coba lagi.',
                            confirmButtonColor: '#ba1a1a',
                            confirmButtonText: 'Tutup'
                        });
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    },
                    onClose: function() {
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    }
                });
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: err.message || 'Gagal menghubungi server.',
                    confirmButtonColor: '#ba1a1a',
                    confirmButtonText: 'Tutup'
                });
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }
    </script>
</x-layouts.member>
