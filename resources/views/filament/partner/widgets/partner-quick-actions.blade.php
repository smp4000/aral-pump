@php
    use App\Filament\Partner\Resources\Stations\StationResource;
@endphp

{{--
    Bewusst kompakte Schnellaktionen: Der Partner gelangt ohne Umweg zu den
    häufigsten Aufgaben. Noch nicht freigeschaltete Module werden nicht als
    funktionslose Schaltflächen vorgetäuscht.
--}}
<x-filament-widgets::widget>
    <x-filament::section heading="Schnellaktionen">
        <div class="station-quick-actions">
            <a class="station-quick-action" href="{{ StationResource::getUrl('create') }}">
                <x-heroicon-o-plus-circle />
                <span>Tankstelle anlegen</span>
            </a>

            <a class="station-quick-action" href="{{ StationResource::getUrl('index') }}">
                <x-heroicon-o-map-pin />
                <span>Tankstellen verwalten</span>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
