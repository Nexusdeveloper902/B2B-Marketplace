@switch($name)
    @case('package')
        <svg viewBox="0 0 48 48" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M24 5 41 14v20L24 43 7 34V14z"/><path d="M7 14l17 9 17-9M24 23v20"/><path d="m15.5 9.5 17 9"/></svg>
        @break
    @case('apps')
        <svg viewBox="0 0 48 48" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="6" width="15" height="15" rx="2"/><rect x="27" y="6" width="15" height="15" rx="2"/><rect x="6" y="27" width="15" height="15" rx="2"/><circle cx="34.5" cy="34.5" r="7.5"/></svg>
        @break
    @case('custom')
        <svg viewBox="0 0 48 48" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M6 24V8h16l20 20-16 16z"/><circle cx="15" cy="17" r="3"/></svg>
        @break
    @case('flow')
        <svg viewBox="0 0 48 48" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="4" y="15" width="12" height="18" rx="2"/><path d="M20 24h8"/><path d="M32 12c6 3 10 7 10 12s-4 9-10 12"/><path d="M30 18c3 1.5 5 3.6 5 6s-2 4.5-5 6"/></svg>
        @break
@endswitch
