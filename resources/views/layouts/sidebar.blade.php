<ul class="menu-inner py-1">

    {{-- ================================
         UTAMA
    ================================ --}}
    <li class="menu-header small text-uppercase">
        <span class="menu-header-text" data-i18n="Utama">Utama</span>
    </li>


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

    @can('backup_read')
        <li class="menu-item" id="backup">
            <a href="{{ route('backup.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-data"></i>
                <div class="text-truncate" data-i18n="Backup Database">Backup Database</div>
            </a>
        </li>
    @endcan

    @can('versi_read')
        <li class="menu-item" id="versi">
            <a href="{{ route('versi.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-time"></i>
                <div class="text-truncate" data-i18n="Versi Aplikasi">Versi Aplikasi</div>
            </a>
        </li>
    @endcan

    <li class="menu-item" id="log-activity">
        <a href="{{ url('/log-viewer') }}" class="menu-link" target="_blank">
            <i class="menu-icon tf-icons bx bx-list-ul"></i>
            <div class="text-truncate" data-i18n="Log Activity">Log Activity</div>
        </a>
    </li>

    @if (app()->environment('local', 'development'))
        <li class="menu-item" id="database-reset">
            <a href="{{ route('system.database-reset.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-reset text-danger"></i>
                <div class="text-truncate text-danger" data-i18n="Database Reset">Database Reset</div>
            </a>
        </li>
        <li class="menu-item" id="database-convert">
            <a href="{{ route('system.database-convert.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-transfer text-warning"></i>
                <div class="text-truncate text-warning" data-i18n="Database Convert">Database Convert</div>
            </a>
        </li>
    @endif

</ul>
