<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\Clientes;
use App\Models\Facturas;
use App\Models\Presupuestos;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class ViewController extends Controller
{
    public function index(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $this->admin_rol();

        // Fetch all items
        $clientes = Clientes::all();
        $activities = AccessLog::all();
        $presupuestos = Presupuestos::all();

        $facturas = Facturas::whereIn('estado', [2, 5])->with(['cobros', 'cliente'])->get(); // Para calcular el balance
        $facturas_estados = Facturas::select('id', 'estado', 'total', 'fechaInicio')->with('cobros')->get();


        $users = User::whereNotIn('rol', ['admin', 'super'])->where('blocked', 0)->get();


        // Retrieve data for charts (raw format) and filter by current year
        $datos_facturas = $this->getTotalsByDate('facturas', 'fechaInicio', [2, 5]);
        $datos_presupuestos = $this->getTotalsByDate('presupuestos', 'fechaInicio', [3, 4]);

        // Format the data to match chart format
        $facturasFormatted = $this->formatChartData($datos_facturas);
        $presupuestosFormatted = $this->formatChartData($datos_presupuestos);
        //$balanceFormatted = $this->formatChartData($balanceFormatted);

        // Return data to Vue
        return Inertia::render('Dashboard', [
            'activities' => $activities,
            'clientes' => $clientes,
            'presupuestos' => $presupuestos,
            'facturas' => $facturas,
            'facturas_estados' => $facturas_estados,
            'user' => $user,
            'users' => $users,
            'chartData' => [
                'facturas' => $facturasFormatted,
                'presupuestos' => $presupuestosFormatted,
            ],
        ]);
    }

    public function getTotalsByDate($table, $dateColumn, $estados = null)
    {
        // Check if the 'estado' column exists in the table
        if (Schema::hasColumn($table, 'estado')) {
            return DB::table($table)
                ->select(DB::raw('SUM(total) as total_mes, YEAR(' . $dateColumn . ') as year, MONTH(' . $dateColumn . ') as mes'))
                ->groupBy('year', 'mes')
                ->whereIn('estado', $estados)
                ->get()
                ->groupBy('year');
        } else {
            return DB::table($table)
                ->select(DB::raw('SUM(total) as total_mes, YEAR(' . $dateColumn . ') as year, MONTH(' . $dateColumn . ') as mes'))
                ->groupBy('year', 'mes')
                ->get()
                ->groupBy('year');
        }
    }

    private function formatChartData($data)
    {
        // Fixed month labels (static) for the chart
        $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $dataset = []; // Initialize as an empty array to store data for each year

        // Variables to calculate statistics
        $currentMonth = date('n'); // Current month as an integer (1-12)
        $currentYear = date('Y');
        $currentMonthTotal = 0;
        $lastYearMonthTotal = 0;

        // Loop through the data to populate the dataset and calculate statistics
        foreach ($data as $year => $months) {
            // Initialize the year's dataset with zeros for each month
            $yearDataset = array_fill(0, 12, 0);

            foreach ($months as $month) {
                $monthIndex = $month->mes - 1; // Convert 1-based month to 0-based index

                // Populate the dataset for the corresponding year
                $yearDataset[$monthIndex] = $month->total_mes;

                // Calculate statistics for the current month and last year
                if ($month->mes == $currentMonth) {
                    if ($year == $currentYear) {
                        $currentMonthTotal = $month->total_mes;
                    } elseif ($year == ($currentYear - 1)) {
                        $lastYearMonthTotal = $month->total_mes;
                    }
                }
            }

            // Add the dataset for the current year to the main dataset
            $dataset[$year] = $yearDataset;
        }

        // Calculate the percentage difference for the current month
        $percentageDifference = 0;
        if ($lastYearMonthTotal > 0) {
            $percentageDifference = (($currentMonthTotal - $lastYearMonthTotal) / $lastYearMonthTotal) * 100;
        }

        // Format the percentage as a string with a plus or minus sign
        $percentageString = round($percentageDifference, 2) . '%';

        return [
            'labels' => $labels, // Month labels for the chart
            'data' => $dataset, // Chart dataset for all years
            'percentageDifference' => $percentageString, // Percentage change
        ];
    }
}
