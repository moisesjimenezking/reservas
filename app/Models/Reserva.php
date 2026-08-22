<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[Fillable(['user_id', 'mesa_ids', 'fecha_reserva', 'hora_inicio', 'hora_fin', 'cantidad_personas', 'estado'])]
class Reserva extends Model
{
    protected $table = 'reservas';

    protected $casts = [
        'mesa_ids' => 'array',
        'fecha_reserva' => 'date',
    ];

    /**
     * Normalize legacy double-encoded values like '"[1,2]"' back to [1, 2].
     */
    protected function mesaIds(): Attribute
    {
        return Attribute::get(function ($value) {
            $decoded = is_array($value)
                ? $value
                : json_decode((string) $value, true);

            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            return is_array($decoded) ? array_map('intval', $decoded) : [];
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all Mesa models for this reservation.
     */
    public function mesas()
    {
        return Mesa::whereIn('id', $this->mesa_ids)->get();
    }

    /**
     * Scope for confirmed reservations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('estado', 'confirmada');
    }

    /**
     * Scope by date range.
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('fecha_reserva', [$start, $end]);
    }

    /**
     * Get the location label from the first table.
     */
    public function getLocationLabelAttribute(): string
    {
        $primaryId = is_array($this->mesa_ids) ? ($this->mesa_ids[0] ?? null) : json_decode($this->mesa_ids, true)[0] ?? null;
        if (!$primaryId) return '';
        $mesa = Mesa::find($primaryId);
        return $mesa?->ubicacion_label ?? '';
    }

    /**
     * Number of tables in this reservation.
     */
    public function getMesasCountAttribute(): int
    {
        return count(is_array($this->mesa_ids) ? $this->mesa_ids : json_decode($this->mesa_ids, true));
    }
}
