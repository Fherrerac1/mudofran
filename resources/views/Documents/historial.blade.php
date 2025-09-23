<!-- resources/views/historials/index.blade.php -->



@section('content')
<div class="container">
    <h1>Historial de Cambios</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Modelo Afectado</th>
                <th>Acción</th>
                <th>Cambios</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($historials as $historial)
                <tr>
                    <td>{{ $historial->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ class_basename($historial->historiable_type) }}</td>
                    <td>{{ ucfirst($historial->action) }}</td>
                    <td>
                        <ul>
                            @foreach(json_decode($historial->changes, true) as $field => $value)
                                <li><strong>{{ $field }}:</strong> {{ $value }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
