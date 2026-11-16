<x-layouts.admin title="Manajemen Anggota">
    <div class="flex flex-col w-full h-full pb-16 relative bg-background font-body-md text-on-background">
        <!-- Header Section -->
        <div class="w-full bg-surface-container-low px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 pb-8 sm:pb-12 mb-6 sm:mb-8 relative z-10 rounded-b-3xl shadow-sm">
            <div class="max-w-container-max mx-auto flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                <div class="flex flex-col gap-2">
                    <span class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider">Keanggotaan Perpustakaan</span>
                    <h1 class="text-display-lg-mobile sm:text-display-lg font-display-lg text-primary m-0 p-0 leading-none">Daftar Anggota</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-xl mt-2">
                        Kelola data keanggotaan siswa dan dewan guru, pantau status keaktifan serta sirkulasi peminjaman.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.members.create') }}" class="flex items-center gap-2 bg-primary text-on-primary hover:bg-primary/90 px-5 py-2.5 rounded-xl transition-colors shadow-md font-body-sm text-body-sm font-semibold">
                        <span class="material-symbols-outlined text-[20px]">person_add</span>
                        Tambah Anggota
                    </a>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <form method="GET" action="{{ route('admin.members.index') }}" class="max-w-container-max mx-auto mt-6 sm:mt-8 flex flex-col md:flex-row gap-3 sm:gap-4">
                <div class="flex-1 relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari berdasarkan nama, NIS, NIP, atau email..."
                        class="w-full bg-surface-container-lowest text-on-surface font-body-sm text-body-sm pl-12 pr-4 py-3 rounded-xl border border-outline-variant focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary transition-shadow shadow-sm"
                    >
                </div>
                <div class="flex flex-wrap gap-4">
                    <div class="relative min-w-[160px]">
                        <select
                            name="tipe"
                            onchange="this.form.submit()"
                            class="w-full bg-surface-container-lowest text-on-surface font-body-sm text-body-sm pl-4 pr-10 py-3 rounded-xl border border-outline-variant focus:outline-none focus:border-secondary appearance-none cursor-pointer shadow-sm"
                        >
                            <option value="">Semua Tipe</option>
                            <option value="siswa" {{ request('tipe') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ request('tipe') === 'guru' ? 'selected' : '' }}>Guru</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">expand_more</span>
                    </div>
                    <div class="relative min-w-[160px]">
                        <select
                            name="status"
                            onchange="this.form.submit()"
                            class="w-full bg-surface-container-lowest text-on-surface font-body-sm text-body-sm pl-4 pr-10 py-3 rounded-xl border border-outline-variant focus:outline-none focus:border-secondary appearance-none cursor-pointer shadow-sm"
                        >
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">expand_more</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="trashed" value="1" id="trashed" {{ request('trashed') ? 'checked' : '' }} onchange="this.form.submit()" class="w-4 h-4 rounded border-outline-variant text-secondary focus:ring-secondary">
                        <label for="trashed" class="text-body-sm text-on-surface-variant cursor-pointer">Tampilkan yang Dinonaktifkan</label>
                    </div>
                    @if(request('q') || request('tipe') || request('status') || request('trashed'))
                        <a href="{{ route('admin.members.index') }}" class="px-4 py-3 bg-surface-container hover:bg-surface-container-high rounded-xl text-on-surface-variant font-body-sm text-body-sm flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Main Content Area -->
        <div class="px-4 sm:px-6 lg:px-8 max-w-container-max mx-auto w-full flex-1">
            <!-- Summary Stats Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-6 sm:mb-8 -mt-2">
                <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-white">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">group</span>
                    </div>
                    <div>
                        <p class="font-label-caps text-label-caps text-on-surface-variant uppercase mb-1">Total Anggota</p>
                        <p class="font-headline-md text-headline-md text-on-surface">{{ \App\Models\User::where('role', 'member')->count() }}</p>
                    </div>
                </div>
                <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-full bg-secondary-container/20 flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">school</span>
                    </div>
                    <div>
                        <p class="font-label-caps text-label-caps text-on-surface-variant uppercase mb-1">Siswa Terdaftar</p>
                        <p class="font-headline-md text-headline-md text-on-surface">{{ \App\Models\User::where('role', 'member')->where('member_type', 'siswa')->count() }}</p>
                    </div>
                </div>
                <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-full bg-tertiary-container/20 flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">badge</span>
                    </div>
                    <div>
                        <p class="font-label-caps text-label-caps text-on-surface-variant uppercase mb-1">Guru Terdaftar</p>
                        <p class="font-headline-md text-headline-md text-on-surface">{{ \App\Models\User::where('role', 'member')->where('member_type', 'guru')->count() }}</p>
                    </div>
                </div>
                <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    </div>
                    <div>
                        <p class="font-label-caps text-label-caps text-on-surface-variant uppercase mb-1">Anggota Aktif</p>
                        <p class="font-headline-md text-headline-md text-on-surface">{{ \App\Models\User::where('role', 'member')->where('status', 'active')->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Members Table -->
            <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/50 overflow-hidden flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container text-on-surface font-title-sm text-title-sm border-b border-outline-variant/50">
                                <th class="py-3 px-3 font-semibold">Anggota</th>
                                <th class="py-3 px-3 font-semibold">NIS / NIP</th>
                                <th class="py-3 px-3 font-semibold">Tipe</th>
                                <th class="py-3 px-3 font-semibold">Kelas / Mapel</th>
                                <th class="py-3 px-3 font-semibold">Kontak</th>
                                <th class="py-3 px-3 font-semibold text-center">Status</th>
                                <th class="py-3 px-3 font-semibold text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-sm text-body-sm text-on-surface divide-y divide-outline-variant/30">
                            @forelse ($members as $member)
                                <tr class="hover:bg-surface-container-low/50 transition-colors {{ $member->trashed() ? 'bg-error/5' : '' }}">
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-3">
                                            @if ($member->avatar)
                                                <img src="{{ $member->avatar }}" alt="{{ $member->name }}" class="w-10 h-10 rounded-full object-cover border border-outline-variant/50">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-secondary-container/20 text-secondary font-bold flex items-center justify-center text-sm">
                                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                                </div>
                                            @endif
                                            <div>
                                                @if ($member->trashed())
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-semibold text-error block">
                                                            {{ $member->name }}
                                                        </span>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-error/10 text-error">Dinonaktifkan</span>
                                                    </div>
                                                @else
                                                    <a href="{{ route('admin.members.show', $member) }}" class="font-semibold text-primary hover:text-secondary block">
                                                        {{ $member->name }}
                                                    </a>
                                                @endif
                                                <span class="text-xs text-on-surface-variant font-mono">{{ $member->username ?? $member->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-on-surface">
                                        {{ $member->isSiswa() ? ($member->nis ?? '-') : ($member->nip ?? '-') }}
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @if ($member->isSiswa())
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-secondary-fixed/50 text-on-secondary-fixed-variant text-xs font-semibold">
                                                Siswa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-tertiary-fixed/60 text-on-tertiary-fixed text-xs font-semibold">
                                                Guru
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-on-surface">
                                        @if ($member->isSiswa())
                                            <span>{{ $member->class ?? '-' }}</span>
                                            @if($member->major)
                                                <span class="text-xs text-on-surface-variant block">{{ $member->major }}</span>
                                            @endif
                                        @else
                                            <span>{{ $member->subject ?? 'Guru SMKN 5' }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <span class="text-xs text-on-surface block">{{ $member->email }}</span>
                                        <span class="text-xs text-on-surface-variant block mt-0.5">{{ $member->phone ?? '-' }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        @if ($member->trashed())
                                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded text-[11px] font-bold uppercase tracking-wider bg-error-container text-error">
                                                Dinonaktifkan
                                            </span>
                                        @elseif ($member->status === 'active')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded text-[11px] font-bold uppercase tracking-wider bg-secondary-container/20 text-secondary">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded text-[11px] font-bold uppercase tracking-wider bg-error-container text-error">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            @if ($member->trashed())
                                                <!-- Actions for trashed members -->
                                                <form method="POST" action="{{ route('admin.members.restore', $member->id) }}" class="inline confirm-action"
                                                    data-confirm-title="Aktifkan Kembali Anggota"
                                                    data-confirm="Apakah Anda yakin ingin mengaktifkan kembali akun anggota '{{ $member->name }}'?"
                                                    data-confirm-icon="question"
                                                    data-confirm-button-text="Ya, Aktifkan!"
                                                    data-confirm-button-color="#059669">
                                                    @csrf
                                                    <button type="submit" class="p-1.5 rounded-md text-secondary hover:bg-secondary-container/20 transition-colors cursor-pointer border-none bg-transparent" title="Aktifkan Kembali">
                                                        <span class="material-symbols-outlined text-[18px]">restore</span>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.members.force-delete', $member->id) }}" class="inline confirm-action"
                                                    data-confirm-title="Hapus Permanen Anggota"
                                                    data-confirm="Apakah Anda yakin ingin menghapus permanen anggota '{{ $member->name }}'? Tindakan ini tidak dapat dibatalkan!"
                                                    data-confirm-icon="warning"
                                                    data-confirm-button-text="Ya, Hapus!"
                                                    data-confirm-button-color="#dc2626">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 rounded-md text-error hover:bg-error-container transition-colors cursor-pointer border-none bg-transparent" title="Hapus Permanen">
                                                        <span class="material-symbols-outlined text-[18px]">delete_forever</span>
                                                    </button>
                                                </form>
                                            @else
                                                <!-- Actions for active members -->
                                                <a href="{{ route('admin.members.show', $member) }}" class="p-1.5 rounded-md text-on-surface-variant hover:bg-surface-container hover:text-secondary transition-colors cursor-pointer border-none bg-transparent" title="Lihat Profil">
                                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                                </a>
                                                <a href="{{ route('admin.members.edit', $member) }}" class="p-1.5 rounded-md text-on-surface-variant hover:bg-surface-container hover:text-secondary transition-colors cursor-pointer border-none bg-transparent" title="Edit Anggota">
                                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                                </a>
                                                <form method="POST" action="{{ route('admin.members.destroy', $member) }}" class="inline confirm-action"
                                                    data-confirm-title="Nonaktifkan Anggota"
                                                    data-confirm="Apakah Anda yakin ingin menonaktifkan anggota '{{ $member->name }}' dari sistem?"
                                                    data-confirm-icon="warning"
                                                    data-confirm-button-text="Ya, Nonaktifkan!"
                                                    data-confirm-button-color="#dc2626">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 rounded-md text-error hover:bg-error-container transition-colors cursor-pointer border-none bg-transparent" title="Nonaktifkan">
                                                        <span class="material-symbols-outlined text-[18px]">power_settings_new</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-on-surface-variant">
                                        <span class="material-symbols-outlined text-5xl mb-2 text-outline-variant">person_off</span>
                                        <p class="font-title-sm text-title-sm">Tidak ada anggota yang sesuai dengan kriteria pencarian.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($members->hasPages())
                    <div class="px-6 py-4 border-t border-outline-variant/30 bg-surface-container-low/40">
                        {{ $members->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
