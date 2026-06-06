<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center px-5 py-2.5
                bg-orange border border-transparent rounded-sm
                text-sm font-medium text-white
                hover:bg-orange/90 active:bg-orange/80
                focus:outline-none focus:ring-2 focus:ring-orange/40 focus:ring-offset-2
                disabled:opacity-50 disabled:cursor-not-allowed
                transition duration-150'
]) }}>
    {{ $slot }}
</button>
