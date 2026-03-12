@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-slate-200 bg-white/90 shadow-sm focus:border-slate-700 focus:ring-slate-700']) }}>
