<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class LogAktivitas extends Component
{
    use WithPagination;

    public function render()
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->paginate(20);

        return view('livewire.admin.log-aktivitas', [
            'logs' => $logs,
        ]);
    }
}
