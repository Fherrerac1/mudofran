# VerifactuController Documentation

## Overview

The `VerifactuController` is responsible for managing the integration with AEAT’s "VeriFactu" SOAP web service. It handles:

-   Sending invoices
-   Annulment (cancellation) of invoices
-   Sending invoice rectifications
-   Generating QR codes for invoice validation

This controller relies on:

-   A digital certificate (`.pfx`) for secure SOAP communication
-   XML templates rendered via Laravel Blade views
-   SOAP calls to AEAT's Verifactu system
-   QR code generation using `SimpleSoftwareIO\QrCode`

---

## Constructor

### Purpose

Initializes the SOAP client with the required certificate, settings, and WSDL.

### Key Actions

-   Converts a `.pfx` certificate to `.pem` if not already generated.
-   Loads certificate path and password from environment or hardcoded values.
-   Sets SOAP options including local certificate, context, and service endpoints.

### Environment Variables Used

```env
AEAT_SOAP_LOCATION=https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP
```
