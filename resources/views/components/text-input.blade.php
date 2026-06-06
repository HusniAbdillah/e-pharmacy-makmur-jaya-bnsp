@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'w-full border border-oat bg-white rounded-sm px-3 py-2 text-sm text-black placeholder-gray-50
                    focus:outline-none focus:ring-2 focus:ring-orange/30 focus:border-orange
                    disabled:bg-cream disabled:text-gray-50 disabled:cursor-not-allowed
                    transition duration-150'
    ]) }}
>
