<nav class="breadcrumb" aria-label="{{ __('store.breadcrumb.aria') }}">
    <ol>
        <li><a href="{{ route('landing') }}">{{ __('store.breadcrumb.home') }}</a></li>
        <li aria-current="page">{{ $current }}</li>
    </ol>
</nav>
