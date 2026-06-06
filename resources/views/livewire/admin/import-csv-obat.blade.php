<div class="min-h-screen bg-cream py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Import CSV Obat</h1>
            <p class="text-gray-50">Upload file CSV untuk menambah data obat</p>
        </div>

        @if(session('success'))
            <div class="bg-semantic-success/10 border border-semantic-success/30 text-semantic-success rounded-sm p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border border-oat rounded-lg p-6">
            <form wire:submit.prevent="upload">
                <div class="mb-4">
                    <label class="form-label">Pilih File CSV</label>
                    <input type="file" wire:model="csvFile" accept=".csv" class="form-input">
                    @error('csvFile') <span class="text-xs text-semantic-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-6">
                    <p class="text-sm text-gray-50 mb-2">Format CSV yang diharapkan:</p>
                    <div class="bg-cream rounded-sm p-3 font-mono text-xs">
                        kategori_obat_id,name,description,composition,dose,side_effects,price,minimum_stock,requires_prescription
                    </div>
                </div>

                <button type="submit" class="btn-dark w-full">
                    <x-heroicon-o-arrow-up-tray class="w-5 h-5 inline mr-2" />
                    Upload & Proses
                </button>
            </form>
        </div>
    </div>
</div>
