<ul class="menu-inner py-1">
    {{-- ================================
         SISTEM
    ================================ --}}
    <li class="menu-header small text-uppercase">
        <span class="menu-header-text" data-i18n="Sistem">Sistem</span>
    </li>

    @can('filescanner_read')
        <li class="menu-item" id="filescanner">
            <a href="{{ route('filescanner.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shield-alt-2"></i>
                <div class="text-truncate" data-i18n="File Scanner">File Scanner</div>
            </a>
        </li>
    @endcan

</ul>
