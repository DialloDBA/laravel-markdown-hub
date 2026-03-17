@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'input-field' . ($disabled ? ' opacity-50 cursor-not-allowed bg-slate-50' : '')
    ]) }}
>
