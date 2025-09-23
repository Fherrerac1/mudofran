Tarea: MUD-015
View Controller: Se cambiara para que acepte el estado 2 y 5 en base a:
    public function getEstadoTextAttribute()
    {
        return match ($this->estado) {
            0 => 'Pendiente',
            1 => 'Rechazada',
            2 => 'Pagado',
            3 => 'Rectificada',
            4 => 'Aceptado',
            5 => 'Pagado Parcialmente',
            default => 'Desconocido',
        };
    }
