<sum:RegFactuSistemaFacturacion
    xmlns:sum="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd"
    xmlns:sum1="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd"
    xmlns:xd="http://www.w3.org/2000/09/xmldsig#">

    <sum:Cabecera>
        <sum1:ObligadoEmision>
            <sum1:NombreRazon>{{ htmlspecialchars($factura->business_name) }}</sum1:NombreRazon>
            <sum1:NIF>{{ htmlspecialchars($factura->tax_id) }}</sum1:NIF>
        </sum1:ObligadoEmision>
    </sum:Cabecera>

    <sum:RegistroFactura>
        <sum1:RegistroAnulacion>
            <sum1:IDVersion>1.0</sum1:IDVersion>

            <sum1:IDFactura>
                <sum1:IDEmisorFacturaAnulada>{{ htmlspecialchars($factura->tax_id) }}</sum1:IDEmisorFacturaAnulada>
                <sum1:NumSerieFacturaAnulada>{{ htmlspecialchars($factura->numFactura) }}</sum1:NumSerieFacturaAnulada>
                <sum1:FechaExpedicionFacturaAnulada>
                    {{ \Carbon\Carbon::parse($factura->fechaInicio)->format('d-m-Y') }}
                </sum1:FechaExpedicionFacturaAnulada>
            </sum1:IDFactura>

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
                <sum1:NombreRazon>{{ htmlspecialchars($factura->business_name) }}</sum1:NombreRazon>
                <sum1:NIF>{{ htmlspecialchars($factura->tax_id) }}</sum1:NIF>
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
            <sum1:Huella>{{ htmlspecialchars($factura->hash) }}</sum1:Huella>
        </sum1:RegistroAnulacion>
    </sum:RegistroFactura>
</sum:RegFactuSistemaFacturacion>
