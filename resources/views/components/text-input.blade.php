@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-slate-200 bg-white/90 shadow-sm focus:border-cyan-600 focus:ring-cyan-600']) }}>
