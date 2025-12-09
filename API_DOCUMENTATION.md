# API DOCUMENTATION - Sistem Administrasi Organisasi

## Base URL
```
http://localhost:8000/api
```

## Authentication
Sebagian besar endpoint memerlukan authentication menggunakan Laravel Sanctum.
Header yang diperlukan:
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

---

## 0. AUTHENTICATION

### 0.1 Register (Public)

#### POST /api/register
Mendaftarkan user baru.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890"
}
```

**Response (201):**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "status": "active"
  },
  "access_token": "1|laravel_sanctum_token_here",
  "token_type": "Bearer"
}
```

---

### 0.2 Login (Public)

#### POST /api/login
Login dan mendapatkan access token.

**Request Body:**
```json
{
  "email": "admin@organisasi.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "admin@organisasi.com",
    "status": "active",
    "roles": [
      {
        "id": 1,
        "name": "Super Admin",
        "slug": "super-admin"
      }
    ]
  },
  "access_token": "2|laravel_sanctum_token_here",
  "token_type": "Bearer"
}
```

**Error Response (401):**
```json
{
  "message": "Invalid login credentials"
}
```

---

### 0.3 Get Current User (Protected)

#### GET /api/me
Mendapatkan informasi user yang sedang login.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "admin@organisasi.com",
    "phone": null,
    "status": "active",
    "roles": [
      {
        "id": 1,
        "name": "Super Admin",
        "slug": "super-admin"
      }
    ],
    "created_at": "2025-12-05T10:00:00.000000Z"
  }
}
```

---

### 0.4 Logout (Protected)

#### POST /api/logout
Logout dari device saat ini (revoke current token).

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

---

### 0.5 Logout All Devices (Protected)

#### POST /api/logout-all
Logout dari semua device (revoke all tokens).

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Logged out from all devices successfully"
}
```

---

## 1. INVENTORY MANAGEMENT

### 1.1 Inventory Items

#### GET /api/inventory/items
Mendapatkan daftar barang inventaris dengan pagination dan filter.

**Query Parameters:**
- `category_id` (optional): Filter by category
- `status` (optional): available, unavailable, discontinued
- `low_stock` (optional): true untuk barang dengan stock rendah
- `search` (optional): Cari berdasarkan nama atau kode
- `per_page` (optional): Jumlah item per halaman (default: 15)

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "code": "BRG-001",
      "name": "Laptop Dell XPS 13",
      "description": "Laptop untuk kegiatan operasional",
      "category": {
        "id": 1,
        "name": "Elektronik"
      },
      "unit": {
        "id": 1,
        "name": "Unit",
        "symbol": "pcs"
      },
      "current_stock": 10,
      "minimum_stock": 5,
      "purchase_price": 15000000,
      "selling_price": 17000000,
      "location": "Gudang A",
      "status": "available"
    }
  ],
  "total": 50
}
```

#### POST /api/inventory/items
Membuat item inventaris baru.

**Request Body:**
```json
{
  "code": "BRG-002",
  "name": "Mouse Wireless",
  "description": "Mouse wireless Logitech",
  "category_id": 1,
  "unit_id": 1,
  "current_stock": 20,
  "minimum_stock": 10,
  "purchase_price": 150000,
  "selling_price": 200000,
  "location": "Gudang B",
  "status": "available"
}
```

#### GET /api/inventory/items/{id}
Mendapatkan detail item dengan riwayat transaksi (10 terakhir).

#### PUT /api/inventory/items/{id}
Update item inventaris (stock tidak bisa diubah langsung, hanya via transaksi).

#### DELETE /api/inventory/items/{id}
Soft delete item inventaris.

#### GET /api/inventory/items/low-stock
Mendapatkan daftar barang dengan stock di bawah minimum.

#### GET /api/inventory/items/out-of-stock
Mendapatkan daftar barang yang stock-nya habis.

---

### 1.2 Inventory Transactions

#### GET /api/inventory/transactions
Mendapatkan daftar transaksi inventaris.

**Query Parameters:**
- `type` (optional): in, out, adjustment
- `inventory_item_id` (optional): Filter by item
- `start_date` (optional): Filter tanggal mulai
- `end_date` (optional): Filter tanggal akhir

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "transaction_code": "INV-IN-20251205-0001",
      "inventory_item": {
        "id": 1,
        "name": "Laptop Dell XPS 13",
        "code": "BRG-001"
      },
      "type": "in",
      "quantity": 5,
      "price_per_unit": 15000000,
      "total_price": 75000000,
      "stock_before": 10,
      "stock_after": 15,
      "transaction_date": "2025-12-05",
      "reference_number": "PO-2025-001",
      "notes": "Pembelian batch baru",
      "recorder": {
        "id": 1,
        "name": "Admin"
      }
    }
  ]
}
```

#### POST /api/inventory/transactions
Membuat transaksi inventaris (in/out/adjustment).

**Request Body:**
```json
{
  "inventory_item_id": 1,
  "type": "in",
  "quantity": 5,
  "price_per_unit": 15000000,
  "transaction_date": "2025-12-05",
  "reference_number": "PO-2025-001",
  "notes": "Pembelian dari vendor"
}
```

**Validasi:**
- Type "out" akan cek ketersediaan stock
- Type "adjustment" akan langsung set stock ke quantity yang diberikan
- Auto-generate transaction_code
- Auto-update current_stock di inventory_items

#### GET /api/inventory/transactions/{id}
Detail transaksi tertentu.

#### GET /api/inventory/items/{id}/history
Riwayat transaksi untuk item tertentu.

---

## 2. MEMBER MANAGEMENT

### 2.1 Members

#### GET /api/members
Mendapatkan daftar anggota.

**Query Parameters:**
- `status` (optional): active, inactive, suspended
- `search` (optional): Cari berdasarkan nama, kode, atau email

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "member_code": "MBR-001",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "081234567890",
      "address": "Jakarta",
      "status": "active",
      "join_date": "2025-01-01",
      "exit_date": null
    }
  ]
}
```

#### POST /api/members
Membuat anggota baru.

**Request Body:**
```json
{
  "member_code": "MBR-002",
  "name": "Jane Doe",
  "email": "jane@example.com",
  "phone": "081234567891",
  "address": "Bandung",
  "status": "active",
  "join_date": "2025-12-05",
  "notes": "Member baru dari referral"
}
```

#### GET /api/members/{id}
Detail anggota dengan 12 riwayat pembayaran terakhir.

#### PUT /api/members/{id}
Update data anggota.

#### DELETE /api/members/{id}
Soft delete anggota.

#### GET /api/members/active
Mendapatkan daftar anggota aktif saja.

#### GET /api/members/{id}/payments
Riwayat pembayaran anggota tertentu.

---

## 3. PAYMENT MANAGEMENT

### 3.1 Member Payments

#### GET /api/payments
Mendapatkan daftar pembayaran iuran.

**Query Parameters:**
- `member_id` (optional): Filter by member
- `period` (optional): Format YYYY-MM
- `status` (optional): paid, unpaid, partial, late
- `start_date` (optional): Filter tanggal mulai
- `end_date` (optional): Filter tanggal akhir

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "payment_code": "PAY-20251205-0001",
      "member": {
        "id": 1,
        "name": "John Doe",
        "member_code": "MBR-001"
      },
      "period": "2025-12",
      "amount": 100000,
      "payment_date": "2025-12-05",
      "due_date": "2025-12-10",
      "status": "paid",
      "payment_method": "transfer"
    }
  ]
}
```

#### POST /api/payments
Mencatat pembayaran iuran member.

**Request Body:**
```json
{
  "member_id": 1,
  "period": "2025-12",
  "amount": 100000,
  "payment_date": "2025-12-05",
  "due_date": "2025-12-10",
  "status": "paid",
  "payment_method": "transfer",
  "notes": "Pembayaran via BCA",
  "cash_account_id": 1,
  "category_id": 2
}
```

**Proses:**
- Cek duplikasi pembayaran untuk periode yang sama
- Jika status "paid", otomatis create CashTransaction (in)
- Auto-update balance di CashAccount
- Generate payment_code otomatis

#### GET /api/payments/summary
Mendapatkan ringkasan pembayaran per periode.

**Query Parameters:**
- `period` (optional): Format YYYY-MM (default: bulan ini)

**Response:**
```json
{
  "period": "2025-12",
  "total_members": 50,
  "paid_count": 45,
  "unpaid_count": 5,
  "total_amount": 4500000,
  "payment_rate": 90.00
}
```

#### GET /api/payments/unpaid/{period}
Mendapatkan daftar member yang belum bayar untuk periode tertentu.

**Response:**
```json
[
  {
    "id": 5,
    "member_code": "MBR-005",
    "name": "Member Name",
    "email": "member@example.com",
    "phone": "081234567890"
  }
]
```

---

## 4. CASH MANAGEMENT

### 4.1 Cash Accounts

#### GET /api/cash/accounts
Mendapatkan daftar akun kas/bank.

**Query Parameters:**
- `type` (optional): bank, cash
- `is_active` (optional): true/false

**Response:**
```json
[
  {
    "id": 1,
    "name": "Kas Utama",
    "code": "KAS-001",
    "type": "cash",
    "account_number": null,
    "balance": 5000000,
    "description": "Kas untuk operasional harian",
    "is_active": true
  },
  {
    "id": 2,
    "name": "Bank BCA",
    "code": "BNK-BCA-001",
    "type": "bank",
    "account_number": "1234567890",
    "balance": 25000000,
    "is_active": true
  }
]
```

#### POST /api/cash/accounts
Membuat akun kas/bank baru.

#### PUT /api/cash/accounts/{id}
Update akun (balance tidak bisa diubah langsung).

---

### 4.2 Cash Transactions

#### GET /api/cash/transactions
Mendapatkan daftar transaksi kas.

**Query Parameters:**
- `cash_account_id` (optional): Filter by account
- `type` (optional): in, out
- `category_id` (optional): Filter by category
- `start_date` (optional): Filter tanggal mulai
- `end_date` (optional): Filter tanggal akhir

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "transaction_code": "CASH-IN-20251205-0001",
      "cash_account": {
        "id": 1,
        "name": "Kas Utama",
        "code": "KAS-001"
      },
      "category": {
        "id": 2,
        "name": "Iuran Member"
      },
      "type": "in",
      "amount": 100000,
      "transaction_date": "2025-12-05",
      "description": "Pembayaran iuran 2025-12 - John Doe",
      "reference_number": "PAY-20251205-0001"
    }
  ]
}
```

#### POST /api/cash/transactions
Membuat transaksi kas masuk/keluar.

**Request Body:**
```json
{
  "cash_account_id": 1,
  "category_id": 5,
  "type": "out",
  "amount": 500000,
  "transaction_date": "2025-12-05",
  "description": "Pembelian ATK",
  "notes": "Pembelian bulanan",
  "reference_number": "INV-2025-001"
}
```

**Validasi:**
- Type "out" akan cek saldo tersedia
- Auto-generate transaction_code
- Auto-update balance di cash_accounts

#### GET /api/cash/transactions/summary
Mendapatkan ringkasan kas per periode.

**Query Parameters:**
- `start_date` (optional): Default awal bulan ini
- `end_date` (optional): Default akhir bulan ini

**Response:**
```json
{
  "period": {
    "start_date": "2025-12-01",
    "end_date": "2025-12-31"
  },
  "total_in": 10000000,
  "total_out": 7500000,
  "balance": 2500000,
  "total_accounts_balance": 30000000
}
```

#### GET /api/cash/transactions/by-category
Mendapatkan ringkasan transaksi per kategori.

**Query Parameters:**
- `type` (required): in atau out
- `start_date` (optional)
- `end_date` (optional)

**Response:**
```json
[
  {
    "category_id": 2,
    "category": {
      "id": 2,
      "name": "Iuran Member"
    },
    "total": 4500000
  },
  {
    "category_id": 3,
    "category": {
      "id": 3,
      "name": "Donasi"
    },
    "total": 2000000
  }
]
```

---

## 5. MASTER DATA

### 5.1 Categories

#### GET /api/categories
Mendapatkan daftar kategori.

**Query Parameters:**
- `type` (optional): inventory, cash
- `is_active` (optional): true/false

**Response:**
```json
[
  {
    "id": 1,
    "name": "Elektronik",
    "slug": "elektronik",
    "description": "Barang elektronik",
    "type": "inventory",
    "parent_id": null,
    "is_active": true
  }
]
```

#### POST /api/categories
Membuat kategori baru (auto-generate slug dari name).

#### PUT /api/categories/{id}
Update kategori.

---

### 5.2 Units

#### GET /api/units
Mendapatkan daftar unit satuan.

**Response:**
```json
[
  {
    "id": 1,
    "name": "Pieces",
    "symbol": "pcs",
    "description": "Satuan unit",
    "is_active": true
  }
]
```

#### POST /api/units
Membuat unit baru.

---

## ERROR RESPONSES

### Validation Error (422)
```json
{
  "errors": {
    "field_name": [
      "Error message"
    ]
  }
}
```

### Insufficient Stock/Balance (400)
```json
{
  "message": "Insufficient stock",
  "available_stock": 10
}
```

### Server Error (500)
```json
{
  "message": "Transaction failed: Error details"
}
```

---

## WORKFLOW EXAMPLES

### Workflow 1: Input Barang Inventaris Baru
1. GET /api/categories?type=inventory - Ambil kategori
2. GET /api/units - Ambil unit satuan
3. POST /api/inventory/items - Buat item baru
4. POST /api/inventory/transactions (type: in) - Catat stock masuk pertama

### Workflow 2: Pencatatan Iuran Member Bulanan
1. GET /api/members/active - Ambil daftar member aktif
2. GET /api/payments/unpaid/2025-12 - Cek siapa yang belum bayar
3. POST /api/payments (status: paid) - Catat pembayaran
   - Otomatis create CashTransaction
   - Otomatis update balance CashAccount

### Workflow 3: Pengeluaran Kas
1. GET /api/cash/accounts?is_active=true - Pilih akun kas
2. GET /api/categories?type=cash - Pilih kategori pengeluaran
3. POST /api/cash/transactions (type: out) - Catat pengeluaran

### Workflow 4: Laporan Bulanan
1. GET /api/payments/summary?period=2025-12 - Ringkasan pembayaran
2. GET /api/cash/transactions/summary - Ringkasan kas
3. GET /api/cash/transactions/by-category - Breakdown per kategori
4. GET /api/inventory/items/low-stock - Alert stock rendah
