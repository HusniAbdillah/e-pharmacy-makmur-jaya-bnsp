<?php

namespace App\Livewire\Admin;

use App\Jobs\ProcessCsvImportJob;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportCsvObat extends Component
{
    use WithFileUploads;

    public $csvFile;

    public function upload(): void
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'csvFile.required' => 'File CSV wajib dipilih.',
            'csvFile.mimes'    => 'File harus berformat CSV.',
            'csvFile.max'      => 'Ukuran file maksimal 2MB.',
        ]);

        $path = $this->csvFile->store('imports');

        ProcessCsvImportJob::dispatch($path);

        session()->flash('success', 'File CSV sedang diproses di background.');
        $this->reset('csvFile');
    }

    public function render()
    {
        return view('livewire.admin.import-csv-obat');
    }
}
