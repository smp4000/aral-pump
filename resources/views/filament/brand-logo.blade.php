<div class="station-desk-logo">
    <span class="station-desk-logo-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M7 3h8a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z" />
            <path d="M8 7h6v4H8zM17 8h1.5l1.5 2v7a1.5 1.5 0 0 0 3 0v-5l-2-2" />
        </svg>
    </span>
    <span>
        StationDesk
        <small>{{ filament()->getCurrentPanel()?->getId() === 'admin' ? 'Control Center' : 'Partner Portal' }}</small>
    </span>
</div>
