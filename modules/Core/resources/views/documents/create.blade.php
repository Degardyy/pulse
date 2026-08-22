<x-core::layouts.app :title="'Unggah Dokumen'"
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('core.dashboard')],
        ['label' => 'Dokumen', 'url' => route('core.documents.index')],
        ['label' => 'Unggah'],
    ]">
    <div class="mx-auto max-w-xl px-4 py-8 sm:px-6 lg:py-10">
        <x-core::ui.page-header title="Unggah Dokumen"
            description="Lingkup menentukan siapa yang dapat membaca dokumen ini." />

        <form method="POST" action="{{ route('core.documents.store') }}" enctype="multipart/form-data"
              class="space-y-6"
              x-data="{ visibility: '{{ old('visibility', $departments->isNotEmpty() ? 'department' : ($divisions->isNotEmpty() ? 'division' : 'paljaya')) }}' }">
            @csrf

            <div>
                <label for="title" class="mb-1.5 block text-sm font-medium text-ink">Judul</label>
                <input id="title" name="title" type="text" required maxlength="150" value="{{ old('title') }}"
                       class="focusable w-full rounded-lg bg-surface px-3.5 py-2.5 text-sm text-ink ring-1 ring-line-2 focus:ring-accent">
                @error('title') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="mb-1.5 block text-sm font-medium text-ink">
                    Deskripsi <span class="font-normal text-ink-3">(opsional)</span>
                </label>
                <textarea id="description" name="description" rows="2" maxlength="1000"
                          class="focusable w-full rounded-lg bg-surface px-3.5 py-2.5 text-sm text-ink ring-1 ring-line-2 focus:ring-accent">{{ old('description') }}</textarea>
                @error('description') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="category" class="mb-1.5 block text-sm font-medium text-ink">
                    Kategori <span class="font-normal text-ink-3">(opsional — mis. SOP, SK, Laporan)</span>
                </label>
                <input id="category" name="category" type="text" maxlength="50" value="{{ old('category') }}"
                       class="focusable w-full rounded-lg bg-surface px-3.5 py-2.5 text-sm text-ink ring-1 ring-line-2 focus:ring-accent">
                @error('category') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="file" class="mb-1.5 block text-sm font-medium text-ink">File</label>
                <input id="file" name="file" type="file" required
                       class="focusable w-full rounded-lg bg-surface text-sm text-ink-2 ring-1 ring-line-2 file:mr-3 file:rounded-l-lg file:border-0 file:bg-surface-2 file:px-3.5 file:py-2.5 file:text-sm file:font-medium file:text-ink">
                <p class="mt-1.5 text-xs text-ink-3">PDF, Office, gambar, zip — maksimal 20 MB.</p>
                @error('file') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>

            <fieldset>
                <legend class="mb-1.5 text-sm font-medium text-ink">Lingkup pembaca</legend>
                <div class="space-y-2 rounded-lg bg-surface-2/50 p-4 ring-1 ring-line">
                    @if ($departments->isNotEmpty())
                        <label class="flex items-start gap-3 text-sm">
                            <input type="radio" name="visibility" value="department" x-model="visibility"
                                   class="mt-0.5 size-4 border-line-2 text-accent focus:ring-accent">
                            <span>
                                <span class="font-medium text-ink">Department</span>
                                <span class="block text-xs text-ink-3">Hanya anggota department (dan kepala division di atasnya)</span>
                            </span>
                        </label>
                        <div x-show="visibility === 'department'" x-cloak class="pl-7">
                            <select name="department_id" class="focusable w-full rounded-lg bg-surface px-3 py-2 text-sm text-ink ring-1 ring-line-2 focus:ring-accent">
                                @foreach ($departments as $id => $name)
                                    <option value="{{ $id }}" @selected((int) old('department_id') === $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if ($divisions->isNotEmpty())
                        <label class="flex items-start gap-3 text-sm">
                            <input type="radio" name="visibility" value="division" x-model="visibility"
                                   class="mt-0.5 size-4 border-line-2 text-accent focus:ring-accent">
                            <span>
                                <span class="font-medium text-ink">Division</span>
                                <span class="block text-xs text-ink-3">Seluruh anggota division, termasuk semua department-nya</span>
                            </span>
                        </label>
                        <div x-show="visibility === 'division'" x-cloak class="pl-7">
                            <select name="division_id" class="focusable w-full rounded-lg bg-surface px-3 py-2 text-sm text-ink ring-1 ring-line-2 focus:ring-accent">
                                @foreach ($divisions as $id => $name)
                                    <option value="{{ $id }}" @selected((int) old('division_id') === $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('division_id') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @php $orgAllowed = $canPublishOrg || $canRequestOrg; @endphp
                    <label class="flex items-start gap-3 text-sm {{ $orgAllowed ? '' : 'opacity-50' }}">
                        <input type="radio" name="visibility" value="paljaya" x-model="visibility" @disabled(! $orgAllowed)
                               class="mt-0.5 size-4 border-line-2 text-accent focus:ring-accent">
                        <span>
                            <span class="font-medium text-ink">Seluruh Paljaya</span>
                            <span class="block text-xs text-ink-3">
                                @if ($canPublishOrg)
                                    Dapat dibaca semua pengguna PULSE — terbit langsung
                                @elseif ($canRequestOrg)
                                    Dapat dibaca semua pengguna PULSE — melalui persetujuan Corporate Secretary
                                @else
                                    Butuh keanggotaan unit atau izin publikasi organisasi
                                @endif
                            </span>
                        </span>
                    </label>
                </div>
                @error('visibility') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </fieldset>

            <div class="flex items-center gap-3 border-t border-line pt-6">
                <button type="submit"
                        class="focusable rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-white transition-colors duration-150 hover:bg-accent-strong">
                    Unggah &amp; Bagikan
                </button>
                <a href="{{ route('core.documents.index') }}"
                   class="focusable rounded-lg px-4 py-2.5 text-sm font-medium text-ink-2 transition-colors hover:bg-surface-2">Batal</a>
            </div>
        </form>
    </div>
</x-core::layouts.app>
