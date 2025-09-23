<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Scopes\TenantScope;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
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
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'tenant_id',
        'email',
        'password',
        'rol',
        'position',
        'telefono',
        'fecha_alta',
        'fecha_nacimiento',
        'img',
        'num_seguridad',
        'localidad',
        'provincia',
        'direccion',
        'cp',
        'coste_hora',
        'dni',
        'horario_semanal',
        'dias_laborables',
        'blocked',

        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['last_appearance'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Devuelve una lista de puestos disponibles.
     *
     * @return array Una lista de arreglos asociativos, cada uno con un texto y un valor que representa una posición.
     */

    public static function puestosDisponibles(): array
    {
        return [
            ['text' => 'Adminstrativo', 'value' => 'Adminstrativo'],
            ['text' => 'Contable', 'value' => 'Contable'],
            ['text' => 'Jefe Tecnico', 'value' => 'Jefe Tecnico'],
            ['text' => 'Adminstrativo Tienda', 'value' => 'Adminstrativo Tienda'],
            ['text' => 'Tienda', 'value' => 'Tienda'],
            ['text' => 'Trabajador', 'value' => 'Trabajador'],
        ];
    }

    /**
     * Devuelve una lista de roles disponibles.
     *
     * @return array Una lista de arreglos asociativos, cada uno con un texto y un valor que representa un rol.
     */
    public static function rolesDisponibles(): array
    {
        return [
            ['text' => 'Gestor', 'value' => 'gestor'],
            ['text' => 'Secretario', 'value' => 'secretario'],
            ['text' => 'Tecnico', 'value' => 'tecnico'],
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    // Relation with TimeRecord model
    public function timeRecords()
    {
        return $this->hasMany(TimeRecord::class);
    }
    public function nominas()
    {
        return $this->hasMany(Nomina::class, 'user_id');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'user_id');
    }

    public function timeOffs()
    {
        return $this->hasMany(TimeOff::class, 'user_id');
    }

    // Accessor to get the last appearance
    public function getLastAppearanceAttribute()
    {
        $lastRecord = $this->timeRecords()
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastRecord && $lastRecord->estado === 1 && is_null($lastRecord->time_out)) {
            return 'activo';
        } else {
            return $lastRecord ? (new DateTime($lastRecord->time_out))->format('d-m-Y H:i:s') : null;
        }
    }

    public function hoursWorkedToday()
    {
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();

        return $this->timeRecords()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->get()
            ->sum(function ($timeRecord) {
                if (!$timeRecord->time_in) {
                    return 0;
                }

                $fecha = $timeRecord->created_at->toDateString();
                $start = Carbon::parse("$fecha {$timeRecord->time_in}");
                $end = $timeRecord->time_out
                    ? Carbon::parse("$fecha {$timeRecord->time_out}")
                    : Carbon::now();

                if ($end->lessThan($start)) {
                    $end->addDay(); // handle cross-midnight shifts
                }

                $pause = $timeRecord->pause_total_seconds ?? 0;
                $meal = $timeRecord->meal_total_seconds ?? 0;

                $workedSeconds = max(0, $start->diffInSeconds($end) - $pause - $meal);

                return round($workedSeconds / 3600, 2); // Return as decimal hours
            });
    }


    public function hoursWorkedThisWeek()
    {
        $startOfWeek = Carbon::now()->startOfWeek(); // Monday 00:00
        $endOfWeek = Carbon::now()->endOfWeek();     // Sunday 23:59:59

        return $this->timeRecords()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get()
            ->sum(function ($timeRecord) {
                if (!$timeRecord->time_in) {
                    return 0;
                }

                $fecha = $timeRecord->created_at->toDateString();
                $start = Carbon::parse("$fecha {$timeRecord->time_in}");
                $end = $timeRecord->time_out
                    ? Carbon::parse("$fecha {$timeRecord->time_out}")
                    : Carbon::now();

                if ($end->lessThan($start)) {
                    $end->addDay(); // Crosses midnight
                }

                $pause = $timeRecord->pause_total_seconds ?? 0;
                $meal = $timeRecord->meal_total_seconds ?? 0;

                $workedSeconds = max(0, $start->diffInSeconds($end) - $meal);

                return round($workedSeconds / 3600, 2); // Decimal hours
            });
    }


    /**
     * Obtiene un array con los datos de horas trabajadas y pausas para cada dia de la semana anterior.
     *
     * Devuelve un array con 7 elementos, cada uno con la siguiente estructura:
     * [
     *      'dia' => string (Lunes, Martes, etc.),
     *      'tiempo' => string (horas trabajadas en formato 'HHh MMm'),
     * ]
     *
     * Nota: los valores de pausa y trabajadas se acumulan en cada dia de la semana.
     *       Si no hay registros para un dia, se devuelve un valor por defecto de 0h trabajadas y 0m de pausa.
     *
     * @return array
     */
    public function dailyHoursWorkedLast7Days()
    {
        Carbon::setLocale(config('app.locale', 'es'));

        $data = [];
        $today = Carbon::now();

        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->subDays($i);

            $totalSeconds = $this->timeRecords()
                ->whereDate('created_at', $date->toDateString())
                ->get()
                ->sum(function ($timeRecord) use ($date) {
                    if (!$timeRecord->time_in) {
                        return 0;
                    }

                    $fecha = $timeRecord->created_at->toDateString();
                    $start = Carbon::parse("$fecha {$timeRecord->time_in}");
                    $end = $timeRecord->time_out
                        ? Carbon::parse("$fecha {$timeRecord->time_out}")
                        : Carbon::now();

                    if ($end->lessThan($start)) {
                        $end->addDay(); // Cross-midnight shift
                    }

                    $pause = $timeRecord->pause_total_seconds ?? 0;
                    $meal = $timeRecord->meal_total_seconds ?? 0;

                    return max(0, $start->diffInSeconds($end) - $meal);
                });

            $hours = intdiv($totalSeconds, 3600);
            $minutes = intdiv($totalSeconds % 3600, 60);

            $data[] = [
                'day' => ucfirst($date->translatedFormat('l')),
                'time' => sprintf("%02dh %02dm", $hours, $minutes),
            ];
        }

        return array_reverse($data);
    }

    /**
     * Obtiene un array con los datos de horas trabajadas y pausas para cada día de la semana actual.
     *
     * Devuelve un array con 7 elementos, cada uno con la siguiente estructura:
     * [
     *      'day' => string (Lunes, Martes, etc.),
     *      'workedHours' => float (horas trabajadas en formato decimal),
     *      'pauseSeconds' => int (segundos de pausa),
     * ]
     *
     * Nota: los valores de pausa y trabajadas se acumulan en cada día de la semana.
     *       Si no hay registros para un día, se devuelve un valor por defecto de 0h trabajadas y 0s de pausa.
     *
     * @return array
     */
    public function dailyHoursWorkedForChart()
    {
        Carbon::setLocale(config('app.locale', 'es'));

        $data = [];

        $today = Carbon::today();

        // Definimos el inicio y fin de la semana actual (de lunes a domingo)
        $startOfWeek = $today->copy()->startOfWeek(); // Lunes
        $endOfWeek = $today->copy()->endOfWeek();     // Domingo

        // Inicializa todos los días de la semana con valores por defecto (0h trabajadas y 0s de pausa)
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $data[$date->toDateString()] = [
                'day' => ucfirst($date->translatedFormat('l')), // Lunes, Martes, etc.
                'workedHours' => 0,
                'pauseSeconds' => 0,
            ];
        }

        // Obtener registros de la semana actual
        $records = $this->timeRecords()
            ->whereBetween('created_at', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
            ->get();

        // Recorre los registros para calcular horas trabajadas y pausas
        foreach ($records as $record) {
            if (!$record->time_in)
                continue; // Si no hay hora de entrada, se omite

            // Construye el datetime de inicio y fin de la jornada
            $fecha = $record->created_at->toDateString();
            $start = Carbon::parse("$fecha {$record->time_in}");
            $end = $record->time_out
                ? Carbon::parse("$fecha {$record->time_out}")
                : Carbon::now(); // Si no se ha fichado salida, se toma la hora actual

            // Si el turno cruza medianoche (ej: entra a las 22h y sale a las 6h)
            if ($end->lessThan($start)) {
                $end->addDay(); // Turno que cruza medianoche
            }

            // Pausas y comidas en segundos
            $pause = $record->pause_total_seconds ?? 0;
            $meal = $record->meal_total_seconds ?? 0;

            // Calcula tiempo efectivo trabajado (en segundos), restando comidas
            $worked = max(0, $start->diffInSeconds($end) - $meal);

            // Acumula los valores en la fecha correspondiente
            if (isset($data[$fecha])) {
                $data[$fecha]['workedHours'] += round($worked / 3600, 2); // Convierte a horas
                $data[$fecha]['pauseSeconds'] += $pause;
            }
        }

        return array_values($data);
    }

    /**
     * Obtiene un array con los datos de horas trabajadas y pausas del usuario para el día actual.
     *
     * Devuelve un array con las siguientes claves:
     * - `workedHoursDecimal`: Horas trabajadas en formato decimal (2 decimales).
     * - `pauseSeconds`: Segundos de pausa total.
     * - `totalSeconds`: Segundos totales trabajados.
     * - `mealSeconds`: Segundos de comida total.
     *
     * Nota: los valores de pausa y trabajadas se acumulan en cada día de la semana.
     *       Si no hay registros para un día, se devuelve un valor por defecto de 0h trabajadas y 0s de pausa.
     *
     * @return array
     */
    public function workedTodayForKpi()
    {
        // Definir el rango de hoy: desde medianoche hasta las 23:59:59
        $todayStart = Carbon::today(); // 00:00:00 de hoy
        $todayEnd = Carbon::today()->endOfDay(); // 23:59:59 de hoy

        // Obtener todos los registros de fichaje del usuario dentro de ese rango
        $records = $this->timeRecords()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->get();

        $totalSeconds = 0;
        $pauseSeconds = 0;
        $mealSeconds = 0;

        foreach ($records as $record) {
            if (!$record->time_in) {
                continue; // Ignorar si no hay hora de entrada
            }

            $fecha = $record->created_at->toDateString();

            // Crear instancias Carbon con fecha y hora de entrada/salida
            $start = Carbon::parse("$fecha {$record->time_in}");
            $end = $record->time_out
                ? Carbon::parse("$fecha {$record->time_out}")
                : Carbon::now(); // Si no ha fichado salida, se asume la hora actual

            // Si el turno cruza la medianoche (ej. 22:00 → 06:00), se suma un día a la salida
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            // Calcular duración total del registro
            $recordSeconds = $start->diffInSeconds($end);

            // Pausas y comida (si existen, se suman, si no, se usa 0)
            $pause = $record->pause_total_seconds ?? 0;
            $meal = $record->meal_total_seconds ?? 0;

            // Acumular los valores al total diario
            $totalSeconds += $recordSeconds;
            $pauseSeconds += $pause;
            $mealSeconds += $meal;
        }

        return [
            'workedHoursDecimal' => round(($totalSeconds - $mealSeconds) / 3600, 2), // Total con pausa
            'pauseSeconds' => $pauseSeconds,
            'totalSeconds' => $totalSeconds,
            'mealSeconds' => $mealSeconds,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            // Check if the 'img' attribute is being changed
            if ($model->isDirty('img')) {
                $originalimg = $model->getOriginal('img');
                if (!is_null($originalimg) && Storage::exists($originalimg)) {
                    Storage::delete($originalimg);
                }
            }
        });
        // Handle the deleting event
        static::deleting(function ($model) {
            // Check if the 'contenido' attribute is not null and if the file exists before attempting to delete it
            if (!is_null($model->img) && Storage::exists($model->img)) {
                Storage::delete($model->img);
            }
        });
    }
}
