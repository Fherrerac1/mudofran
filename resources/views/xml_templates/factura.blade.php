<sum:RegFactuSistemaFacturacion
    xmlns:sum="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd"
    xmlns:sum1="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd"
    xmlns:xd="http://www.w3.org/2000/09/xmldsig#">

    <sum:Cabecera>
        <sum1:ObligadoEmision>
            <sum1:NombreRazon>{{ $factura->business_name }}</sum1:NombreRazon>
            <sum1:NIF>{{ $factura->tax_id }}</sum1:NIF>
        </sum1:ObligadoEmision>
    </sum:Cabecera>

    <sum:RegistroFactura>
        <sum1:RegistroAlta>
            <sum1:IDVersion>1.0</sum1:IDVersion>

            <sum1:IDFactura>
                <sum1:IDEmisorFactura>{{ $factura->tax_id }}</sum1:IDEmisorFactura>
                <sum1:NumSerieFactura>{{ $factura->numFactura }}</sum1:NumSerieFactura>
                <sum1:FechaExpedicionFactura>{{ \Carbon\Carbon::parse($factura->fechaInicio)->format('d-m-Y') }}
                </sum1:FechaExpedicionFactura>
            </sum1:IDFactura>

            <sum1:NombreRazonEmisor>{{ $factura->business_name }}</sum1:NombreRazonEmisor>
            <sum1:TipoFactura>F1</sum1:TipoFactura>
            <sum1:DescripcionOperacion>Servicios informáticos</sum1:DescripcionOperacion>

            <sum1:Destinatarios>
                <sum1:IDDestinatario>
                    <sum1:NombreRazon>{{ $factura->cliente_nombre }}
                    </sum1:NombreRazon>
                    <sum1:NIF>{{ $factura->cliente_nif }}</sum1:NIF>
                </sum1:IDDestinatario>
            </sum1:Destinatarios>

            <sum1:Desglose>
                <sum1:DetalleDesglose>
                    <sum1:ClaveRegimen>01</sum1:ClaveRegimen>
                    <sum1:CalificacionOperacion>S1</sum1:CalificacionOperacion>
                    <sum1:TipoImpositivo>{{ number_format($factura->iva, 0, '', '') }}</sum1:TipoImpositivo>
                    <sum1:BaseImponibleOimporteNoSujeto>{{ number_format($factura->total_sin_iva, 2, '.', '') }}
                    </sum1:BaseImponibleOimporteNoSujeto>
                    <sum1:CuotaRepercutida>{{ number_format($factura->total_iva, 2, '.', '') }}
                    </sum1:CuotaRepercutida>
                </sum1:DetalleDesglose>
            </sum1:Desglose>

            <sum1:CuotaTotal>{{ number_format($factura->total_iva, 2, '.', '') }}</sum1:CuotaTotal>
            <sum1:ImporteTotal>{{ number_format($factura->total, 2, '.', '') }}</sum1:ImporteTotal>

            <sum1:Encadenamiento>
                <sum1:RegistroAnterior>
                    <sum1:IDEmisorFactura>{{ $factura->tax_id }}</sum1:IDEmisorFactura>

                    <sum1:NumSerieFactura>
                        {{ isset($factura->ultimaFactura) ? $factura->ultimaFactura->numFactura : 'XXX' }}
                    </sum1:NumSerieFactura>

                    <sum1:FechaExpedicionFactura>
                        {{ isset($factura->ultimaFactura) && $factura->ultimaFactura->fechaInicio
                            ? \Carbon\Carbon::parse($factura->ultimaFactura->fechaInicio)->format('d-m-Y')
                            : 'XXX' }}
                    </sum1:FechaExpedicionFactura>

                    <sum1:Huella>{{ isset($factura->ultimaFactura) ? $factura->ultimaFactura->hash : 'XXX' }}
                    </sum1:Huella>
                </sum1:RegistroAnterior>
            </sum1:Encadenamiento>


            <sum1:SistemaInformatico>
                <sum1:NombreRazon>{{ $factura->business_name }}</sum1:NombreRazon>
                <sum1:NIF>{{ $factura->tax_id }}</sum1:NIF>
                <sum1:NombreSistemaInformatico>NombreSistemaInformatico</sum1:NombreSistemaInformatico>
                <sum1:IdSistemaInformatico>77</sum1:IdSistemaInformatico>
                <sum1:Version>1.0.03</sum1:Version>
                <sum1:NumeroInstalacion>383</sum1:NumeroInstalacion>
                <sum1:TipoUsoPosibleSoloVerifactu>N</sum1:TipoUsoPosibleSoloVerifactu>
                <sum1:TipoUsoPosibleMultiOT>S</sum1:TipoUsoPosibleMultiOT>
                <sum1:IndicadorMultiplesOT>S</sum1:IndicadorMultiplesOT>
            </sum1:SistemaInformatico>

            <sum1:FechaHoraHusoGenRegistro>{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i:sP') }}
            </sum1:FechaHoraHusoGenRegistro>
            <sum1:TipoHuella>01</sum1:TipoHuella>
            <sum1:Huella>{{ $factura->hash ?? 'Huella' }}</sum1:Huella>

        </sum1:RegistroAlta>
    </sum:RegistroFactura>

</sum:RegFactuSistemaFacturacion>
