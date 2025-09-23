# Verifactu API Usage Examples

This document provides practical examples of how to interact with the Verifactu API using HTTP requests for creating, canceling, and rectifying invoices.

---

## 📤 Example: Create Invoice

**Endpoint:**

```http
POST /api/verifactu/send/15
```

**Request Payload (Sent to Verifactu):**

```json
{
  "tax_id": "A12345678",
  "business_name": "Mi Empresa S.L.",
  "api_key": "your_api_key_here",
  "numFactura": "FAC-2025-001",
  "cliente_nombre": "Ana García",
  "cliente_nif": "12345678Z",
  "fechaInicio": "2025-06-25",
  "hash": "d4a9f3e1c9a84e9b8f8b8c3e245a6b27",
  "total_sin_iva": 100.0,
  "iva": 21.0,
  "total_iva": 21.0,
  "total": 121.0,
  "ultimaFactura": {
    "numFactura": "FAC-2025-000",
    "fechaInicio": "2025-06-10",
    "hash": "e3a1a2c49ab78a61f8e9b0ab54cd12c8"
  }
}
```

**Response:**

```json
{
  "success": true,
  "message": "Factura enviada correctamente.",
  "response": {
    "qr_code": "data:image/png;base64,..."
  }
}
```

---

## ❌ Example: Cancel Invoice

**Endpoint:**

```http
POST /api/verifactu/cancel/15
```

**Request Payload (Sent to Verifactu):**

```json
{
  "tax_id": "A12345678",
  "business_name": "Mi Empresa S.L.",
  "api_key": "your_api_key_here",
  "numFactura": "FAC-2025-001",
  "fechaInicio": "2025-06-25",
  "hash": "d4a9f3e1c9a84e9b8f8b8c3e245a6b27",
  "total": 121.0,
  "ultimaFactura": {
    "numFactura": "FAC-2025-000",
    "fechaInicio": "2025-06-10",
    "hash": "e3a1a2c49ab78a61f8e9b0ab54cd12c8"
  }
}
```

**Response:**

```json
{
  "success": true,
  "message": "Factura Anulada correctamente.",
  "response": {
    "status": "cancelled"
  }
}
```

---

## 🧾 Example: Rectify Invoice

**Endpoint:**

```http
POST /api/verifactu/rectify/20
```

**Request Payload (Sent to Verifactu):**

```json
{
  "tax_id": "A12345678",
  "business_name": "Mi Empresa S.L.",
  "api_key": "your_api_key_here",
  "numFactura": "FAC-2025-002",
  "cliente_nombre": "Ana García",
  "cliente_nif": "12345678Z",
  "fechaInicio": "2025-06-28",
  "hash": "aab8e23987c384a1cf09e441eb8231aa",
  "total_sin_iva": 90.0,
  "iva": 18.9,
  "total_iva": 18.9,
  "total": 108.9,
  "facturaOriginal": {
    "numFactura": "FAC-2025-001",
    "fechaInicio": "2025-06-25",
    "hash": "d4a9f3e1c9a84e9b8f8b8c3e245a6b27"
  }
}
```

**Response:**

```json
{
  "success": true,
  "message": "Factura rectificada enviada correctamente.",
  "response": {
    "qr_code": "data:image/png;base64,..."
  }
}
```

---

These examples illustrate the typical payload structures and expected responses when working with the Verifactu API integration. Make sure to replace all placeholder values with your actual invoice and client data.
