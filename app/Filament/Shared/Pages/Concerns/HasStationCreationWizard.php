<?php

namespace App\Filament\Shared\Pages\Concerns;

use App\Exceptions\StationLookupException;
use App\Filament\Shared\Schemas\StationForm;
use App\Models\Brand;
use App\Services\StationLookupService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Gemeinsamer Anlage-Wizard für Plattform-Admin und Tankstellenpartner.
 *
 * Schritt 1 sucht bei Benzinpreis-Aktuell.de nach Stationen zur eingegebenen
 * PLZ und übernimmt die Auswahl. Schritt 2 zeigt weiterhin das vollständige
 * Stammdatenformular, damit alle automatisch erkannten Werte geprüft und
 * betriebliche Zusatzangaben direkt ergänzt werden können.
 */
trait HasStationCreationWizard
{
    use HasWizard;

    /** @var array<int, array<string, mixed>> */
    public array $stationSearchResults = [];

    public ?string $stationSearchLocation = null;

    public int $stationSearchRadiusKm = 5;

    /** @return array<int, Step> */
    public function getSteps(): array
    {
        return [
            Step::make('Standort suchen')
                ->description('PLZ eingeben und Tankstelle im Umkreis auswählen')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    TextInput::make('station_search_postal_code')
                        ->label('Postleitzahl')
                        ->placeholder('z. B. 36100')
                        ->helperText('PLZ des gewünschten Standorts eingeben.')
                        ->required()
                        ->regex('/^\d{5}$/')
                        ->maxLength(5)
                        ->dehydrated(false),
                    ToggleButtons::make('station_search_radius')
                        ->label('Umkreis festlegen')
                        ->options([
                            0 => 'Exakt',
                            3 => '3 km',
                            5 => '5 km',
                            10 => '10 km',
                            20 => '20 km',
                        ])
                        ->default(5)
                        ->inline()
                        ->required()
                        ->dehydrated(false),
                    Actions::make([
                        Action::make('searchNearbyStations')
                            ->label('Tankstellen suchen')
                            ->icon('heroicon-o-magnifying-glass')
                            ->color('primary')
                            ->action(fn ($livewire) => $livewire->searchNearbyStations()),
                    ]),
                    Placeholder::make('station_search_summary')
                        ->label('Suchergebnis')
                        ->content(fn ($livewire): string => $livewire->getStationSearchSummary()),
                    Placeholder::make('station_search_attribution')
                        ->hiddenLabel()
                        ->content(new HtmlString('Tankstellendaten: <a href="https://www.benzinpreis-aktuell.de" target="_blank" rel="noopener noreferrer">Benzinpreis-Aktuell.de</a>.')),
                    Select::make('station_search_result')
                        ->label('Tankstelle auswählen')
                        ->placeholder('Name, Marke oder Straße durchsuchen ...')
                        ->options(fn ($livewire): array => $livewire->getStationSearchOptions())
                        ->searchable()
                        ->allowHtml()
                        ->native(false)
                        ->visible(fn ($livewire): bool => $livewire->stationSearchResults !== [])
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (mixed $state, $livewire) => $livewire->applyStationSearchResult($state === null ? null : (string) $state))
                        ->dehydrated(false),
                    // Diese beiden Felder werden nicht angezeigt, aber mit dem
                    // Datensatz gespeichert und ermöglichen spätere Abgleiche.
                    Hidden::make('source_provider'),
                    Hidden::make('source_station_id'),
                ])
                ->columns(1),
            Step::make('Stammdaten prüfen')
                ->description('Automatisch übernommene Daten kontrollieren und ergänzen')
                ->icon('heroicon-o-clipboard-document-check')
                ->schema(StationForm::components($this->stationWizardIncludesPartnerSelection())),
        ];
    }

    /** Führt die zwischengespeicherte Umkreissuche auf dem Laravel-Server aus. */
    public function searchNearbyStations(): void
    {
        $postalCode = trim((string) data_get($this->data, 'station_search_postal_code'));
        $radiusKm = (int) data_get($this->data, 'station_search_radius', 5);

        if (! preg_match('/^\d{5}$/', $postalCode)) {
            Notification::make()
                ->danger()
                ->title('Postleitzahl prüfen')
                ->body('Bitte geben Sie eine deutsche Postleitzahl mit genau fünf Ziffern ein.')
                ->send();

            return;
        }

        try {
            $result = app(StationLookupService::class)->searchByPostalCode($postalCode, $radiusKm);
        } catch (StationLookupException $exception) {
            $this->stationSearchResults = [];
            $this->stationSearchLocation = null;
            data_set($this->data, 'station_search_result', null);

            Notification::make()
                ->danger()
                ->title('Tankstellensuche nicht möglich')
                ->body($exception->getMessage())
                ->send();

            return;
        }

        $this->stationSearchResults = $result['stations'];
        $this->stationSearchLocation = $result['location']['name'];
        $this->stationSearchRadiusKm = $result['radius_km'];
        data_set($this->data, 'station_search_result', null);

        Notification::make()
            ->success()
            ->title(count($this->stationSearchResults).' Tankstellen gefunden')
            ->body('Wählen Sie die passende Tankstelle aus der Liste aus.')
            ->send();
    }

    /** @return array<string, array<string, string>> */
    public function getStationSearchOptions(): array
    {
        $stations = collect($this->stationSearchResults);

        $format = function (array $station): array {
            $address = trim("{$station['street']} {$station['house_number']} {$station['postal_code']} {$station['city']}");
            $name = e((string) $station['name']);
            $safeAddress = e($address);
            $brandLogo = $this->stationBrandLogo((string) ($station['brand'] ?? $station['name']));

            $label = <<<HTML
                <span class="station-search-option">
                    {$brandLogo}
                    <span class="station-search-option__content">
                        <strong>{$name}</strong>
                        <small>{$safeAddress}</small>
                    </span>
                </span>
                HTML;

            return [(string) $station['source_station_id'] => $label];
        };

        return [
            'Geöffnete Tankstellen' => $stations->where('is_open', true)->mapWithKeys($format)->all(),
            'Geschlossen / kein Dieselpreis' => $stations->where('is_open', false)->mapWithKeys($format)->all(),
        ];
    }

    public function getStationSearchSummary(): string
    {
        if ($this->stationSearchLocation === null) {
            return 'Noch keine Suche ausgeführt.';
        }

        return $this->stationSearchRadiusKm === 0
            ? sprintf('%d Tankstellen exakt in %s.', count($this->stationSearchResults), $this->stationSearchLocation)
            : sprintf(
                '%d Tankstellen im Umkreis von %d km um %s.',
                count($this->stationSearchResults),
                $this->stationSearchRadiusKm,
                $this->stationSearchLocation,
            );
    }

    /**
     * Übernimmt die gewählte Station in das zweite Wizard-Formular. Alle
     * Werte bleiben dort editierbar; externe Daten werden nie ungeprüft fixiert.
     */
    public function applyStationSearchResult(?string $stationId): void
    {
        if ($stationId === null) {
            return;
        }

        $station = collect($this->stationSearchResults)
            ->firstWhere('source_station_id', $stationId);

        if (! is_array($station)) {
            return;
        }

        try {
            $station = app(StationLookupService::class)->loadStationDetails($station);
        } catch (StationLookupException $exception) {
            Notification::make()
                ->danger()
                ->title('Tankstellendetails nicht verfügbar')
                ->body($exception->getMessage())
                ->send();

            return;
        }

        $this->data = array_replace($this->data ?? [], [
            'source_provider' => $station['source_provider'],
            'source_station_id' => $station['source_station_id'],
            'name' => $station['name'],
            'brand_id' => $this->findBrandId((string) $station['brand']),
            'street' => $station['street'],
            'house_number' => $station['house_number'],
            'postal_code' => $station['postal_code'],
            'city' => $station['city'],
            'country' => 'DE',
            'latitude' => $station['latitude'],
            'longitude' => $station['longitude'],
            'fuel_types' => $station['fuel_types'] ?? [],
            'opening_hours' => $station['opening_hours'] ?? [],
            'price_super' => $station['price_super'],
            'price_e10' => $station['price_e10'],
            'price_diesel' => $station['price_diesel'],
            'prices_updated_at' => $station['prices_updated_at'],
            'settings' => array_filter([
                'benzinpreis_detail' => $station['source_detail_slug'] ?? null,
                'benzinpreis_mts_id' => $station['source_mts_id'] ?? null,
                'benzinpreis_url' => $station['source_url'] ?? null,
            ]),
        ]);

        Notification::make()
            ->success()
            ->title('Tankstellendaten übernommen')
            ->body('Bitte prüfen und ergänzen Sie jetzt die Stammdaten im nächsten Schritt.')
            ->send();
    }

    /** Plattform-Admins wählen zusätzlich den zugehörigen Partner aus. */
    protected function stationWizardIncludesPartnerSelection(): bool
    {
        return false;
    }

    private function findBrandId(string $externalBrand): ?int
    {
        $normalizedExternalBrand = $this->normalizeBrandName($externalBrand);

        if ($normalizedExternalBrand === '') {
            return null;
        }

        $brand = Brand::query()
            ->where('is_active', true)
            ->get(['id', 'name', 'slug'])
            ->first(function (Brand $brand) use ($normalizedExternalBrand): bool {
                return in_array($normalizedExternalBrand, [
                    $this->normalizeBrandName($brand->name),
                    $this->normalizeBrandName($brand->slug),
                ], true);
            });

        if ($brand === null && str_contains($normalizedExternalBrand, 'frei')) {
            $brand = Brand::query()->where('slug', 'freie-tankstelle')->first();
        }

        return $brand?->id;
    }

    private function normalizeBrandName(string $brand): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(Str::ascii($brand))) ?? '';
    }

    /**
     * Erstellt ein kompaktes, markenbezogenes Erkennungszeichen für die
     * Suchauswahl. So bleibt die Liste auch ohne eingeblendete Preise schnell
     * erfassbar. Für unbekannte Marken wird ein neutrales Tankstellenzeichen
     * verwendet.
     */
    private function stationBrandLogo(string $brand): string
    {
        $normalizedBrand = $this->normalizeBrandName($brand);
        $brandStyles = [
            'aral' => ['AR', '#0050aa', '#ffffff'],
            'shell' => ['SH', '#ffd500', '#d71920'],
            'totalenergies' => ['TE', '#ed1b2f', '#ffffff'],
            'esso' => ['ES', '#ffffff', '#005daa'],
            'jet' => ['JET', '#ffd100', '#003b70'],
            'eniagip' => ['ENI', '#ffd500', '#111827'],
            'raiffeisen' => ['R', '#ffcc00', '#111827'],
            'globus' => ['G', '#d71920', '#ffffff'],
            'star' => ['★', '#e30613', '#ffffff'],
            'bft' => ['bft', '#e30613', '#ffffff'],
            'avia' => ['A', '#e30613', '#ffffff'],
        ];

        $style = collect($brandStyles)
            ->first(fn (array $style, string $key): bool => str_contains($normalizedBrand, $key));

        [$shortName, $background, $foreground] = $style ?? ['⛽', '#e8f1ff', '#0050aa'];

        return sprintf(
            '<span class="station-search-option__logo" style="--station-logo-bg:%s;--station-logo-color:%s" aria-hidden="true">%s</span>',
            $background,
            $foreground,
            e($shortName),
        );
    }
}
