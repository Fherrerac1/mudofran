# Verifactu API Documentation

This document provides technical documentation for the Laravel-based API controller that interfaces with the [Verifactu](https://app.blackcatsoluciones.es/) system. The API enables invoice submission, rectification, and cancellation operations.

---

## 🔐 Authentication

The Verifactu API requires authentication via an API key stored in the Laravel environment file:

```env
VERIFACTU_API_KEY=your_verifactu_api_key
```

The API also utilizes configuration values retrieved via a helper:

* `tax_id`
* `business_name`

These should be accessible via the `ConfigHelper::get()` method.

---

## 📤 Endpoint: Send Invoice

**URL:** `POST /api/verifactu/send/{id}`

Sends a specified invoice to the Verifactu API.

### Parameters

* `id` *(integer)*: The ID of the invoice in the local database.

### Description

Retrieves the invoice and previous invoice (if available), builds a payload, and sends it to Verifactu's `/create` endpoint. On success, stores the returned QR code into the invoice record.

### Success Response

```json
{
  "success": true,
  "message": "Factura enviada correctamente.",
  "response": {
    "qr_code": "data:image/png;base64,..."
  }
}
```

### Error Handling

Returns a relevant HTTP status code and message if:

* Invoice not found
* Verifactu API fails

---

## ❌ Endpoint: Cancel Invoice

**URL:** `POST /api/verifactu/cancel/{id}`

Cancels a previously submitted invoice.

### Parameters

* `id` *(integer)*: The ID of the invoice to cancel.

### Description

Builds a cancellation payload and sends it to Verifactu's `/cancel` endpoint.

### Success Response

```json
{
  "success": true,
  "message": "Factura Anulada correctamente.",
  "response": {
    "status": "cancelled"
  }
}
```

### Error Handling

* Invoice not found
* Remote API returns error (included in the response)

---

## 🧾 Endpoint: Rectify Invoice

**URL:** `POST /api/verifactu/rectify/{id}`

Sends a rectified invoice (correction of a previously submitted one).

### Parameters

* `id` *(integer)*: The ID of the rectified invoice

### Requirements

* The invoice must have a related original invoice defined via the `factura_nativa` field.

### Description

Builds a rectification payload and submits it to Verifactu's `/rectificate` endpoint.

### Success Response

```json
{
  "success": true,
  "message": "Factura rectificada enviada correctamente.",
  "response": {
    "qr_code": "data:image/png;base64,..."
  }
}
```

### Error Handling

* Invoice or original invoice not found
* Verifactu returns an error

---

## 📦 Example Payload Structure

All requests to Verifactu include a payload with the following fields:

```json
{
  "tax_id": "A12345678",
  "business_name": "My Business S.L.",
  "api_key": "my_api_key",
  "numFactura": "FAC-2025-001",
  "cliente_nombre": "Juan Pérez",
  "cliente_nif": "12345678Z",
  "fechaInicio": "2025-06-25",
  "hash": "abc123...",
  "total_sin_iva": 100.00,
  "iva": 21.00,
  "total_iva": 21.00,
  "total": 121.00,
  "ultimaFactura": {
    "numFactura": "FAC-2025-000",
    "fechaInicio": "2025-06-10",
    "hash": "xyz789..."
  }
}
```

For rectification, an additional `facturaOriginal` block is included.

---

## 🛠 Notes

* The controller uses Laravel's `Http::timeout()->post()` to send requests.
* QR codes returned are saved in the `qr_code` field of the `facturas` table.
* This API is internal-facing and assumes the invoice data is fully prepared before sending.

---

## ✅ Best Practices

* Ensure `factura_nativa` is populated for rectification invoices.
* Log failed requests for audit and reprocessing.
* Verify `hash` consistency for integrity validation.

---

For questions or integration help, contact the backend team or refer to Verifactu's official API documentation.
