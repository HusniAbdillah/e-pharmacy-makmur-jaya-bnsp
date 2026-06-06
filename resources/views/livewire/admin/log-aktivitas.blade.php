<div class="min-h-screen bg-cream py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Log Aktivitas</h1>
            <p class="text-gray-50">Riwayat aktivitas sistem</p>
        </div>

        <div class="bg-white border border-oat rounded-lg overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/50 border-b border-oat">
                        <tr class="text-left">
                            <th class="p-3 font-medium">ID</th>
                            <th class="p-3 font-medium">Waktu</th>
                            <th class="p-3 font-medium">User</th>
                            <th class="p-3 font-medium">Aksi</th>
                            <th class="p-3 font-medium">Model</th>
                            <th class="p-3 font-medium">Model ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-oat">
                        @forelse($logs as $log)
                            <tr>
                                <td class="p-3">{{ $log->id }}</td>
                                <td class="p-3">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="p-3">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 bg-oat text-black text-xs rounded-sm">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="p-3">{{ $log->model }}</td>
                                <td class="p-3">#{{ $log->model_id ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-50">Tidak ada log aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white border border-oat rounded-lg p-5">
            {{ $logs->links() }}
        </div>
    </div>
</div>
