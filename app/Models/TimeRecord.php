<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeRecord extends Model
{
    protected static function booted()
    {
        // 1️⃣ Apply global tenant scope
        static::addGlobalScope(new TenantScope());

        // 2️⃣ Automatically set tenant_id on create
        static::creating(function ($model) {
            if (session()->has('tenant_id')) {
                $model->tenant_id = session('tenant_id');
            }
        });
    }
    protected $table = 'time_records';

    // Fillable fields for mass assignment
    protected $fillable = [
        'tenant_idF',
        'user_id',
        'time_in',
        'latitude',
        'longitude',
        'time_out',
        'latitude_out',
        'longitude_out',
        'pause_started_at',
        'pause_total_seconds',
        'meal_started_at',
        'meal_total_seconds',
        'estado',
        'obervaciones'
    ];


    // Dates for automatic Carbon instances
    protected $dates = [
        'time_in',
        'time_out',
        'pause_started_at',
        'meal_started_at'
    ];

    // Virtual attributes to append to the model's array and JSON output
    protected $appends = [
        'total',
        'start',
        'out',
        'estado_text',
        'pause_total_formatted',
        'meal_total_formatted',
        'worked_time_formatted',
    ];

    /**
     * Total de horas trabajadas en el registro actual.
     * En el total solo se resta el tiempo de comida del total de segundos entre la hora de entrada y la hora de salida.
     *
     * Resta el tiempo de comida del total de segundos entre la hora de entrada y la hora de salida.
     * Si el registro no tiene hora de salida, devuelve null.
     *
     * @return string|null Formato HH:MM
     */
    public function getTotalAttribute()
    {
        if ($this->time_in && $this->time_out && $this->created_at) {
            $fecha = $this->created_at->toDateString();

            $start = Carbon::parse("$fecha {$this->time_in}");
            $end = Carbon::parse("$fecha {$this->time_out}");

            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $meal = $this->meal_total_seconds ?? 0;
            $totalSeconds = max(0, $start->diffInSeconds($end) - $meal);

            return gmdate('H:i', $totalSeconds);
        }

        return null;
    }

    public function getEstadoTextAttribute()
    {
        return match ($this->estado) {
            0 => 'Pendiente',
            1 => 'En Curso',
            2 => 'Aprobado',
            default => 'Desconocido',
        };
    }

    /**
     * Devuelve los estados disponibles con su texto asociado
     *
     * @return array
     */
    public static function getEstados(): array
    {
        return [
            ['value' => 0, 'text' => 'Pendiente'],
            ['value' => 1, 'text' => 'En Curso'],
            ['value' => 2, 'text' => 'Aprobado'],
        ];
    }

    public function getPauseTotalFormattedAttribute()
    {
        $totalSeconds = $this->pause_total_seconds ?? 0;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function getMealTotalFormattedAttribute()
    {
        return gmdate('H:i:s', (int) $this->meal_total_seconds);
    }
    /**
     * Relationship: A TimeRecord belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Accessor for 'start' attribute.
     * Formats 'time_in' to 'H:i' (hours and minutes).
     */
    public function getStartAttribute()
    {
        return $this->time_in ? Carbon::parse($this->time_in)->format('H:i') : null;
    }

    /**
     * Accessor for 'out' attribute.
     * Formats 'time_out' to 'H:i' (hours and minutes).
     */
    public function getOutAttribute()
    {
        return $this->time_out ? Carbon::parse($this->time_out)->format('H:i') : null;
    }

    /**
     * Calcula el tiempo trabajado en formato 'HH:MM', restando el tiempo de pausa y de comida
     * del total de segundos entre la hora de entrada y la hora de salida.
     *
     * Si la hora de salida es anterior a la hora de entrada, se asume que el registro cruza
     * la medianoche y se ajusta el tiempo de salida.
     *
     * @return string|null Devuelve el tiempo trabajado en formato 'HH:MM' o null si falta información.
     */

    public function getWorkedTimeFormattedAttribute()
    {
        if ($this->time_in && $this->time_out && $this->created_at) {
            $fecha = $this->created_at->toDateString();

            $start = Carbon::parse("$fecha {$this->time_in}");
            $end = Carbon::parse("$fecha {$this->time_out}");

            if ($end->lessThan($start)) {
                $end->addDay(); // por si cruza medianoche
            }

            $diffInSeconds = $start->diffInSeconds($end);

            $pause = $this->pause_total_seconds ?? 0;
            $meal = $this->meal_total_seconds ?? 0;

            $effectiveSeconds = max(0, $diffInSeconds - $pause - $meal);

            return gmdate('H:i', $effectiveSeconds);
        }

        return null;
    }
}
