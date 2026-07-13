<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Protokolliert fachliche Änderungen eines Models in einer unveränderlichen Historie.
 *
 * Models können über `$auditExclude` sensible oder rein technische Attribute
 * ausschließen. Dadurch gelangen insbesondere Passwörter, Tokens und Bankdaten
 * niemals in das Audit-Log.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (Model $model) => $model->writeAuditLog('created'));
        static::updated(fn (Model $model) => $model->writeAuditLog('updated'));
        static::deleted(fn (Model $model) => $model->writeAuditLog('deleted'));
        static::restored(fn (Model $model) => $model->writeAuditLog('restored'));
    }

    protected function writeAuditLog(string $event): void
    {
        $excluded = array_merge([
            'created_at', 'updated_at', 'deleted_at', 'remember_token',
        ], property_exists($this, 'auditExclude') ? $this->auditExclude : []);

        $keys = $event === 'updated'
            ? array_keys($this->getChanges())
            : array_keys($this->getAttributes());

        $keys = array_values(array_diff($keys, $excluded));

        if ($event === 'updated' && $keys === []) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach ($keys as $key) {
            if ($event === 'updated') {
                $oldValues[$key] = $this->getOriginal($key);
            }

            if (! in_array($event, ['deleted'], true)) {
                $newValues[$key] = $this->getAttribute($key);
            }
        }

        AuditLog::query()->create([
            'partner_id' => $this->auditPartnerId(),
            'user_id' => auth()->id(),
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => (string) $this->getKey(),
            'event' => $event,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'url' => app()->runningInConsole() ? null : request()->fullUrl(),
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : request()->userAgent(),
        ]);
    }

    /**
     * Ermittelt die Mandantenzuordnung sowohl für direkte Partner-Models als
     * auch für untergeordnete Datensätze wie ein Bankkonto einer Tankstelle.
     */
    protected function auditPartnerId(): ?int
    {
        if (isset($this->partner_id)) {
            return (int) $this->partner_id;
        }

        if (method_exists($this, 'station')) {
            return $this->station()->value('partner_id');
        }

        return null;
    }
}
