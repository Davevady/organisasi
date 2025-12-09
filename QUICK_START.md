# QUICK START GUIDE

## ✅ Setup Selesai!

Database dan sistem sudah berhasil di-setup dengan:
- 10 tabel database dengan relasi lengkap
- 4 Roles (Super Admin, Admin, Treasurer, Warehouse)
- 8 Categories (4 inventory + 4 cash categories)
- 6 Units (pcs, box, dzn, set, kg, L)
- 2 Cash Accounts (Kas Utama, Bank BCA)

---

## 🚀 Menjalankan Aplikasi

### 1. Start Development Server

```bash
php artisan serve
```

Aplikasi akan berjalan di: http://localhost:8000

### 2. (Optional) Start Queue Worker

Jika menggunakan queue:
```bash
php artisan queue:work
```

### 3. (Optional) Build Frontend Assets

```bash
npm run dev
```

Untuk production:
```bash
npm run build
```

---

## 👤 Membuat User Pertama

### Via Tinker

```bash
php artisan tinker
```

```php
use App\Models\User;
use App\Models\Role;

// Buat user
$user = User::create([
    'name' => 'Super Admin',
    'email' => 'admin@organisasi.com',
    'password' => bcrypt('password123'),
    'status' => 'active'
]);

// Assign role
$role = Role::where('slug', 'super-admin')->first();
$user->roles()->attach($role);

// Verifikasi
echo "User created: " . $user->email . " with role: " . $role->name;
```

### Via Register (Jika Enabled)

1. Buka http://localhost:8000/register
2. Daftar dengan email dan password
3. Login
4. Assign role via tinker (seperti di atas)

---

## 🧪 Testing API Endpoints

### Opsi 1: Register User Baru (Tercepat!)

**POST** `http://localhost:8000/api/register`

Body (JSON):
```json
{
  "name": "Admin User",
  "email": "admin@test.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Response akan langsung berisi `access_token`!

Kemudian assign role via Tinker:
```php
$user = User::where('email', 'admin@test.com')->first();
$role = Role::where('slug', 'super-admin')->first();
$user->roles()->attach($role);
```

### Opsi 2: Login dengan User yang Sudah Ada

**POST** `http://localhost:8000/api/login`

Body (JSON):
```json
{
  "email": "admin@organisasi.com",
  "password": "password123"
}
```

Response:
```json
{
  "message": "Login successful",
  "user": { ... },
  "access_token": "2|laravel_sanctum_token_here",
  "token_type": "Bearer"
}
```

**Copy `access_token` untuk digunakan di request selanjutnya!**

---

### Testing Protected Endpoints

Setelah dapat token, test endpoints berikut:

#### 1. Get Current User Info

**GET** `http://localhost:8000/api/me`

Headers:
```
Authorization: Bearer {your-token}
Accept: application/json
```

#### 2. Test Inventory API

**GET** `http://localhost:8000/api/inventory/items`

Headers:
```
Authorization: Bearer {your-token}
Accept: application/json
```

#### 3. Test Members API

**GET** `http://localhost:8000/api/members`

Headers:
```
Authorization: Bearer {your-token}
Accept: application/json
```

#### 4. Test Cash Accounts

**GET** `http://localhost:8000/api/cash/accounts`

Headers:
```
Authorization: Bearer {your-token}
Accept: application/json
```

---

## 📊 Contoh Data untuk Testing

### Membuat Member

**POST** `/api/members`
```json
{
  "member_code": "MBR-001",
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "081234567890",
  "address": "Jakarta",
  "status": "active",
  "join_date": "2025-01-01"
}
```

### Membuat Inventory Item

**POST** `/api/inventory/items`
```json
{
  "code": "BRG-001",
  "name": "Laptop Dell XPS 13",
  "description": "Laptop untuk operasional",
  "category_id": 1,
  "unit_id": 1,
  "current_stock": 10,
  "minimum_stock": 5,
  "purchase_price": 15000000,
  "selling_price": 17000000,
  "location": "Gudang A",
  "status": "available"
}
```

### Membuat Transaksi Stock In

**POST** `/api/inventory/transactions`
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

### Membuat Pembayaran Iuran

**POST** `/api/payments`
```json
{
  "member_id": 1,
  "period": "2025-12",
  "amount": 100000,
  "payment_date": "2025-12-05",
  "status": "paid",
  "payment_method": "transfer",
  "cash_account_id": 1,
  "category_id": 5
}
```

---

## 📁 Struktur Project

```
organisasi/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/          # 8 API Controllers
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   └── Models/               # 10 Models dengan relasi
│       ├── User.php
│       ├── Role.php
│       ├── Member.php
│       ├── Category.php
│       ├── Unit.php
│       ├── InventoryItem.php
│       ├── InventoryTransaction.php
│       ├── CashAccount.php
│       ├── CashTransaction.php
│       └── MemberPayment.php
├── database/
│   ├── migrations/           # 10 Migration files
│   ├── factories/            # 4 Factories
│   └── seeders/
│       └── InitialDataSeeder.php
├── routes/
│   ├── api.php              # 40+ API endpoints
│   └── web.php
├── API_DOCUMENTATION.md      # Dokumentasi API lengkap
├── DATABASE_SCHEMA.md        # Dokumentasi database
└── QUICK_START.md           # File ini
```

---

## 🔧 Troubleshooting

### Database Connection Error

```bash
# Check .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=organisasi
DB_USERNAME=root
DB_PASSWORD=
```

### Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Re-run Migrations

```bash
php artisan migrate:fresh --seed
```

⚠️ WARNING: Ini akan menghapus semua data!

---

## 📚 Dokumentasi Lengkap

- [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - Semua endpoint API dengan examples
- [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) - ERD dan struktur database
- [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md) - Panduan lengkap implementasi

---

## 🎯 Next Steps

1. **Setup Frontend**
   - Build Vue components untuk inventory, members, payments
   - Integrate dengan API endpoints

2. **Add More Features**
   - Reports & Analytics
   - Export to PDF/Excel
   - Notification system
   - Dashboard charts

3. **Security**
   - Implement fine-grained permissions
   - Add API rate limiting
   - Setup CORS untuk production

4. **Testing**
   - Write feature tests untuk API endpoints
   - Setup CI/CD pipeline

---

## 💡 Tips

- Gunakan Laravel Telescope untuk debugging API
- Install Laravel Debugbar untuk development
- Setup Laravel Horizon untuk queue monitoring
- Use Postman Collection untuk testing API

---

## 📞 Support

Jika menemukan issue:
1. Check error logs di `storage/logs/laravel.log`
2. Baca dokumentasi di folder docs/
3. Check database constraints dan relationships

---

Selamat coding! 🚀
