@props(['status'])

@if ($status)
    <div {{ $attributes->merge([
        'class' => 'bg-semantic-success/10 border border-semantic-success/30 text-semantic-success text-sm rounded-sm px-3 py-2'
    ]) }}>
        {{ $status }}
    </div>
@endif
