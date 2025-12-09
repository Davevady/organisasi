# DATABASE SCHEMA DOCUMENTATION

## Entity Relationship Diagram (ERD) - Textual Representation

```
┌─────────────┐         ┌──────────────┐
│   users     │────────<│   role_user  │>────────┐
└─────────────┘         └──────────────┘         │
      │                                           │
      │                                     ┌──────────┐
      │                                     │  roles   │
      │                                     └──────────┘
      │
      │ (recorded_by)
      │
      ├──────────> inventory_transactions
      │
      ├──────────> cash_transactions
      │
      └──────────> member_payments


┌──────────────────┐         ┌────────────────────────┐
│ categories       │────────<│  inventory_items       │
│ (type=inventory) │         └────────────────────────┘
└──────────────────┘                   │
                                       │
                                       │
                              ┌────────▼─────────────────┐
                              │ inventory_transactions   │
                              └──────────────────────────┘


┌──────────────────┐         ┌────────────────────┐
│ categories       │────────<│ cash_transactions  │
│ (type=cash)      │         └────────────────────┘
└──────────────────┘                   │
                                       │
                              ┌────────▼─────────┐
                              │ cash_accounts    │
                              └──────────────────┘


┌──────────┐         ┌──────────────────┐         ┌────────────────────┐
│ members  │────────<│ member_payments  │>────────│ cash_transactions  │
└──────────┘         └──────────────────┘         └────────────────────┘


┌──────────┐
│  units   │────────< inventory_items
└──────────┘
```

---

## TABLES DETAIL

### 1. users
**Deskripsi:** Tabel user/admin yang mengelola sistem
**Primary Key:** id
**Soft Delete:** No

| Column                    | Type          | Nullable | Default  | Description                |
|---------------------------|---------------|----------|----------|----------------------------|
| id                        | bigint        | No       | AUTO     | Primary key                |
| name                      | varchar(255)  | No       |          | Nama user                  |
| email                     | varchar(255)  | No       |          | Email (unique)             |
| email_verified_at         | timestamp     | Yes      | NULL     | Waktu verifikasi email     |
| password                  | varchar(255)  | No       |          | Password (hashed)          |
| status                    | enum          | No       | active   | active, inactive           |
| phone                     | varchar(255)  | Yes      | NULL     | Nomor telepon              |
| two_factor_secret         | text          | Yes      | NULL     | 2FA secret                 |
| two_factor_recovery_codes | text          | Yes      | NULL     | 2FA recovery codes         |
| two_factor_confirmed_at   | timestamp     | Yes      | NULL     | 2FA confirmation time      |
| remember_token            | varchar(100)  | Yes      | NULL     | Remember token             |
| created_at                | timestamp     | Yes      | NULL     |                            |
| updated_at                | timestamp     | Yes      | NULL     |                            |

**Indexes:**
- UNIQUE: email

**Relations:**
- belongsToMany: Role (via role_user)
- hasMany: InventoryTransaction (via recorded_by)
- hasMany: CashTransaction (via recorded_by)
- hasMany: MemberPayment (via recorded_by)

---

### 2. roles
**Deskripsi:** Tabel role untuk RBAC
**Primary Key:** id

| Column      | Type          | Nullable | Default | Description        |
|-------------|---------------|----------|---------|--------------------|
| id          | bigint        | No       | AUTO    | Primary key        |
| name        | varchar(255)  | No       |         | Nama role          |
| slug        | varchar(255)  | No       |         | Slug (unique)      |
| description | text          | Yes      | NULL    | Deskripsi role     |
| created_at  | timestamp     | Yes      | NULL    |                    |
| updated_at  | timestamp     | Yes      | NULL    |                    |

**Indexes:**
- UNIQUE: name, slug

**Relations:**
- belongsToMany: User (via role_user)

---

### 3. role_user (Pivot Table)
**Deskripsi:** Many-to-Many relationship antara User dan Role
**Primary Key:** id

| Column     | Type      | Nullable | Default | Description              |
|------------|-----------|----------|---------|--------------------------|
| id         | bigint    | No       | AUTO    | Primary key              |
| user_id    | bigint    | No       |         | Foreign key to users     |
| role_id    | bigint    | No       |         | Foreign key to roles     |
| created_at | timestamp | Yes      | NULL    |                          |
| updated_at | timestamp | Yes      | NULL    |                          |

**Indexes:**
- UNIQUE: (user_id, role_id)
- FOREIGN KEY: user_id REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY: role_id REFERENCES roles(id) ON DELETE CASCADE

---

### 4. members
**Deskripsi:** Tabel anggota organisasi
**Primary Key:** id
**Soft Delete:** Yes

| Column       | Type          | Nullable | Default  | Description                      |
|--------------|---------------|----------|----------|----------------------------------|
| id           | bigint        | No       | AUTO     | Primary key                      |
| member_code  | varchar(255)  | No       |          | Kode member (unique)             |
| name         | varchar(255)  | No       |          | Nama member                      |
| email        | varchar(255)  | Yes      | NULL     | Email (unique, nullable)         |
| phone        | varchar(255)  | Yes      | NULL     | Nomor telepon                    |
| address      | text          | Yes      | NULL     | Alamat                           |
| status       | enum          | No       | active   | active, inactive, suspended      |
| join_date    | date          | No       |          | Tanggal bergabung                |
| exit_date    | date          | Yes      | NULL     | Tanggal keluar                   |
| notes        | text          | Yes      | NULL     | Catatan                          |
| created_at   | timestamp     | Yes      | NULL     |                                  |
| updated_at   | timestamp     | Yes      | NULL     |                                  |
| deleted_at   | timestamp     | Yes      | NULL     | Soft delete                      |

**Indexes:**
- UNIQUE: member_code, email

**Relations:**
- hasMany: MemberPayment

---

### 5. categories
**Deskripsi:** Tabel kategori untuk inventaris dan kas
**Primary Key:** id

| Column      | Type          | Nullable | Default | Description                    |
|-------------|---------------|----------|---------|--------------------------------|
| id          | bigint        | No       | AUTO    | Primary key                    |
| name        | varchar(255)  | No       |         | Nama kategori                  |
| slug        | varchar(255)  | No       |         | Slug (unique)                  |
| description | text          | Yes      | NULL    | Deskripsi                      |
| type        | enum          | No       |         | inventory, cash                |
| parent_id   | bigint        | Yes      | NULL    | Self-referencing for hierarchy |
| is_active   | boolean       | No       | true    | Status aktif                   |
| created_at  | timestamp     | Yes      | NULL    |                                |
| updated_at  | timestamp     | Yes      | NULL    |                                |

**Indexes:**
- UNIQUE: slug
- FOREIGN KEY: parent_id REFERENCES categories(id) ON DELETE CASCADE

**Relations:**
- belongsTo: Category (parent)
- hasMany: Category (children)
- hasMany: InventoryItem
- hasMany: CashTransaction

---

### 6. units
**Deskripsi:** Tabel satuan unit untuk inventaris
**Primary Key:** id

| Column      | Type          | Nullable | Default | Description       |
|-------------|---------------|----------|---------|-------------------|
| id          | bigint        | No       | AUTO    | Primary key       |
| name        | varchar(255)  | No       |         | Nama unit         |
| symbol      | varchar(255)  | No       |         | Simbol (unique)   |
| description | text          | Yes      | NULL    | Deskripsi         |
| is_active   | boolean       | No       | true    | Status aktif      |
| created_at  | timestamp     | Yes      | NULL    |                   |
| updated_at  | timestamp     | Yes      | NULL    |                   |

**Indexes:**
- UNIQUE: symbol

**Relations:**
- hasMany: InventoryItem

---

### 7. inventory_items
**Deskripsi:** Tabel barang inventaris
**Primary Key:** id
**Soft Delete:** Yes

| Column         | Type           | Nullable | Default   | Description                        |
|----------------|----------------|----------|-----------|------------------------------------|
| id             | bigint         | No       | AUTO      | Primary key                        |
| code           | varchar(255)   | No       |           | Kode barang (unique)               |
| name           | varchar(255)   | No       |           | Nama barang                        |
| description    | text           | Yes      | NULL      | Deskripsi                          |
| category_id    | bigint         | No       |           | Foreign key to categories          |
| unit_id        | bigint         | No       |           | Foreign key to units               |
| current_stock  | decimal(15,2)  | No       | 0         | Stock saat ini                     |
| minimum_stock  | decimal(15,2)  | No       | 0         | Threshold minimum stock            |
| purchase_price | decimal(15,2)  | No       | 0         | Harga beli                         |
| selling_price  | decimal(15,2)  | No       | 0         | Harga jual                         |
| location       | varchar(255)   | Yes      | NULL      | Lokasi penyimpanan                 |
| status         | enum           | No       | available | available, unavailable, discontinued |
| image          | varchar(255)   | Yes      | NULL      | Path gambar                        |
| created_at     | timestamp      | Yes      | NULL      |                                    |
| updated_at     | timestamp      | Yes      | NULL      |                                    |
| deleted_at     | timestamp      | Yes      | NULL      | Soft delete                        |

**Indexes:**
- UNIQUE: code
- FOREIGN KEY: category_id REFERENCES categories(id) ON DELETE RESTRICT
- FOREIGN KEY: unit_id REFERENCES units(id) ON DELETE RESTRICT

**Relations:**
- belongsTo: Category
- belongsTo: Unit
- hasMany: InventoryTransaction

---

### 8. inventory_transactions
**Deskripsi:** Tabel transaksi inventaris (in/out/adjustment)
**Primary Key:** id

| Column             | Type           | Nullable | Default | Description                    |
|--------------------|----------------|----------|---------|--------------------------------|
| id                 | bigint         | No       | AUTO    | Primary key                    |
| transaction_code   | varchar(255)   | No       |         | Kode transaksi (unique)        |
| inventory_item_id  | bigint         | No       |         | Foreign key to inventory_items |
| type               | enum           | No       |         | in, out, adjustment            |
| quantity           | decimal(15,2)  | No       |         | Jumlah                         |
| price_per_unit     | decimal(15,2)  | Yes      | NULL    | Harga per unit                 |
| total_price        | decimal(15,2)  | Yes      | NULL    | Total harga                    |
| stock_before       | decimal(15,2)  | No       |         | Stock sebelum transaksi        |
| stock_after        | decimal(15,2)  | No       |         | Stock setelah transaksi        |
| transaction_date   | date           | No       |         | Tanggal transaksi              |
| reference_number   | varchar(255)   | Yes      | NULL    | Nomor referensi (PO, Invoice)  |
| notes              | text           | Yes      | NULL    | Catatan                        |
| recorded_by        | bigint         | No       |         | Foreign key to users           |
| created_at         | timestamp      | Yes      | NULL    |                                |
| updated_at         | timestamp      | Yes      | NULL    |                                |

**Indexes:**
- UNIQUE: transaction_code
- INDEX: transaction_date, type
- FOREIGN KEY: inventory_item_id REFERENCES inventory_items(id) ON DELETE RESTRICT
- FOREIGN KEY: recorded_by REFERENCES users(id) ON DELETE RESTRICT

**Relations:**
- belongsTo: InventoryItem
- belongsTo: User (recorder)

---

### 9. cash_accounts
**Deskripsi:** Tabel akun kas/bank
**Primary Key:** id

| Column         | Type           | Nullable | Default | Description             |
|----------------|----------------|----------|---------|-------------------------|
| id             | bigint         | No       | AUTO    | Primary key             |
| name           | varchar(255)   | No       |         | Nama akun               |
| code           | varchar(255)   | No       |         | Kode akun (unique)      |
| type           | enum           | No       |         | bank, cash              |
| account_number | varchar(255)   | Yes      | NULL    | Nomor rekening (jika bank) |
| balance        | decimal(15,2)  | No       | 0       | Saldo saat ini          |
| description    | text           | Yes      | NULL    | Deskripsi               |
| is_active      | boolean        | No       | true    | Status aktif            |
| created_at     | timestamp      | Yes      | NULL    |                         |
| updated_at     | timestamp      | Yes      | NULL    |                         |

**Indexes:**
- UNIQUE: code

**Relations:**
- hasMany: CashTransaction

---

### 10. cash_transactions
**Deskripsi:** Tabel transaksi kas masuk/keluar
**Primary Key:** id

| Column            | Type           | Nullable | Default | Description                  |
|-------------------|----------------|----------|---------|------------------------------|
| id                | bigint         | No       | AUTO    | Primary key                  |
| transaction_code  | varchar(255)   | No       |         | Kode transaksi (unique)      |
| cash_account_id   | bigint         | No       |         | Foreign key to cash_accounts |
| category_id       | bigint         | No       |         | Foreign key to categories    |
| type              | enum           | No       |         | in, out                      |
| amount            | decimal(15,2)  | No       |         | Jumlah                       |
| transaction_date  | date           | No       |         | Tanggal transaksi            |
| description       | varchar(255)   | No       |         | Deskripsi transaksi          |
| notes             | text           | Yes      | NULL    | Catatan                      |
| reference_number  | varchar(255)   | Yes      | NULL    | Nomor referensi              |
| attachment        | varchar(255)   | Yes      | NULL    | Path file bukti              |
| recorded_by       | bigint         | No       |         | Foreign key to users         |
| created_at        | timestamp      | Yes      | NULL    |                              |
| updated_at        | timestamp      | Yes      | NULL    |                              |

**Indexes:**
- UNIQUE: transaction_code
- INDEX: transaction_date, type
- FOREIGN KEY: cash_account_id REFERENCES cash_accounts(id) ON DELETE RESTRICT
- FOREIGN KEY: category_id REFERENCES categories(id) ON DELETE RESTRICT
- FOREIGN KEY: recorded_by REFERENCES users(id) ON DELETE RESTRICT

**Relations:**
- belongsTo: CashAccount
- belongsTo: Category
- belongsTo: User (recorder)

---

### 11. member_payments
**Deskripsi:** Tabel pembayaran iuran member
**Primary Key:** id

| Column               | Type           | Nullable | Default | Description                      |
|----------------------|----------------|----------|---------|----------------------------------|
| id                   | bigint         | No       | AUTO    | Primary key                      |
| payment_code         | varchar(255)   | No       |         | Kode pembayaran (unique)         |
| member_id            | bigint         | No       |         | Foreign key to members           |
| cash_transaction_id  | bigint         | Yes      | NULL    | Foreign key to cash_transactions |
| period               | varchar(255)   | No       |         | Periode (YYYY-MM)                |
| amount               | decimal(15,2)  | No       |         | Jumlah                           |
| payment_date         | date           | No       |         | Tanggal pembayaran               |
| due_date             | date           | Yes      | NULL    | Tanggal jatuh tempo              |
| status               | enum           | No       | unpaid  | paid, unpaid, partial, late      |
| payment_method       | enum           | No       | cash    | cash, transfer, other            |
| notes                | text           | Yes      | NULL    | Catatan                          |
| recorded_by          | bigint         | No       |         | Foreign key to users             |
| created_at           | timestamp      | Yes      | NULL    |                                  |
| updated_at           | timestamp      | Yes      | NULL    |                                  |

**Indexes:**
- UNIQUE: payment_code
- INDEX: period, status, payment_date
- FOREIGN KEY: member_id REFERENCES members(id) ON DELETE RESTRICT
- FOREIGN KEY: cash_transaction_id REFERENCES cash_transactions(id) ON DELETE SET NULL
- FOREIGN KEY: recorded_by REFERENCES users(id) ON DELETE RESTRICT

**Relations:**
- belongsTo: Member
- belongsTo: CashTransaction
- belongsTo: User (recorder)

---

## KEY RELATIONSHIPS SUMMARY

### One-to-Many Relationships
1. **Category → InventoryItem** (1:N)
2. **Unit → InventoryItem** (1:N)
3. **InventoryItem → InventoryTransaction** (1:N)
4. **User → InventoryTransaction** (1:N via recorded_by)
5. **CashAccount → CashTransaction** (1:N)
6. **Category → CashTransaction** (1:N)
7. **User → CashTransaction** (1:N via recorded_by)
8. **Member → MemberPayment** (1:N)
9. **CashTransaction → MemberPayment** (1:1 optional)
10. **User → MemberPayment** (1:N via recorded_by)

### Many-to-Many Relationships
1. **User ←→ Role** (via role_user pivot table)

### Self-Referencing Relationships
1. **Category → Category** (parent-child hierarchy)

---

## BUSINESS RULES

### Inventory Management
1. Stock hanya bisa diubah melalui InventoryTransaction
2. Transaksi "out" harus cek ketersediaan stock
3. Transaksi "adjustment" langsung set stock ke nilai baru
4. Auto-generate transaction_code dengan format: INV-{TYPE}-{DATE}-{SEQUENCE}

### Cash Management
1. Balance di CashAccount hanya bisa diubah melalui CashTransaction
2. Transaksi "out" harus cek saldo tersedia
3. Auto-generate transaction_code dengan format: CASH-{TYPE}-{DATE}-{SEQUENCE}

### Member Payments
1. Satu member hanya bisa punya satu pembayaran per periode
2. Jika status "paid", otomatis create CashTransaction (type: in)
3. Auto-generate payment_code dengan format: PAY-{DATE}-{SEQUENCE}

### Data Integrity
1. Soft delete untuk: Members, InventoryItems
2. Hard delete restricted untuk data yang punya relasi transaksi
3. Semua transaksi harus mencatat user yang melakukan (recorded_by)
