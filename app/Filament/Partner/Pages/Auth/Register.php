<?php

namespace App\Filament\Partner\Pages\Auth;

use App\Models\Partner;
use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use SensitiveParameter;

/**
 * Öffentliche Selbstregistrierung eines neuen Tankstellenpartners.
 *
 * Partner und erster Inhaber werden innerhalb einer Datenbanktransaktion
 * angelegt. Schlägt einer der Schritte fehl, bleibt kein unvollständiger
 * Mandant zurück. Jedes neue Konto startet mit einer 30-tägigen Testphase.
 */
class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label('Unternehmen / Partnerfirma')
                    ->required()
                    ->maxLength(255)
                    ->autofocus(),
                TextInput::make('name')
                    ->label('Vor- und Nachname')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('E-Mail-Adresse')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class),
                TextInput::make('password')
                    ->label('Passwort')
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule(Password::default())
                    ->dehydrateStateUsing(fn (#[SensitiveParameter] string $state): string => Hash::make($state))
                    ->same('passwordConfirmation'),
                TextInput::make('passwordConfirmation')
                    ->label('Passwort wiederholen')
                    ->password()
                    ->revealable()
                    ->required()
                    ->dehydrated(false),
                Checkbox::make('terms_accepted')
                    ->label('Ich akzeptiere die Nutzungsbedingungen und Datenschutzhinweise.')
                    ->accepted()
                    ->required()
                    ->dehydrated(false),
            ]);
    }

    protected function handleRegistration(#[SensitiveParameter] array $data): Model
    {
        return DB::transaction(function () use ($data): User {
            // Der Slug dient später als stabile technische Kennung des Mandanten.
            // Bei identischen Firmennamen wird automatisch eine laufende Nummer ergänzt.
            $baseSlug = Str::slug($data['company_name']) ?: 'partner';
            $slug = $baseSlug;
            $suffix = 2;

            while (Partner::query()->where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            $partner = Partner::query()->create([
                'company_name' => $data['company_name'],
                'slug' => $slug,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(30),
            ]);

            return $partner->users()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'partner_owner',
                'is_active' => true,
            ]);
        });
    }

    public function getHeading(): string
    {
        return '30 Tage kostenlos testen';
    }
}
