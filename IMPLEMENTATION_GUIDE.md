# IMPLEMENTATION GUIDE - Sistem Administrasi Organisasi

## OVERVIEW

Sistem ini adalah aplikasi berbasis web untuk pengelolaan administrasi organisasi dengan fokus utama pada:
1. **Sistem Inventaris** - Pengelolaan barang dengan tracking stock real-time
2. **Sistem Pembayaran Iuran Kas** - Manajemen pembayaran member dan kas organisasi

## TEKNOLOGI STACK

- **Backend:** Laravel 12 (PHP 8.2)
- **Frontend:** Inertia.js + Vue.js (sudah tersedia di starter kit)
- **Database:** MySQL/PostgreSQL/SQLite
- **Authentication:** Laravel Fortify + Sanctum
- **API:** RESTful JSON API

---

## GETTING STARTED

### 1. Setup Database

```bash
# Jalankan migrasi
php artisan migrate

# Atau fresh migrate (hati-hati: akan hapus semua data)
php artisan migrate:fresh
```

### 2. Seed Initial Data (Opsional)

Buat seeder untuk data awal seperti roles, categories, dan units:

```bash
php artisan make:seeder InitialDataSeeder
```

Contoh seeder:

```php
<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Category;
use App\Models\Unit;
use App\Models\CashAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access to all features'
        ]);

        Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Can manage inventory and payments'
        ]);

        Role::create([
            'name' => 'Treasurer',
            'slug' => 'treasurer',
            'description' => 'Can manage cash transactions'
        ]);

        Role::create([
            'name' => 'Warehouse',
            'slug' => 'warehouse',
            'description' => 'Can manage inventory only'
        ]);

        // Inventory Categories
        $electronics = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'type' => 'inventory',
            'description' => 'Peralatan elektronik'
        ]);

        Category::create([
            'name' => 'Laptop',
            'slug' => 'laptop',
            'type' => 'inventory',
            'parent_id' => $electronics->id
        ]);

        Category::create([
            'name' => 'Furniture',
            'slug' => 'furniture',
            'type' => 'inventory',
            'description' => 'Perabotan kantor'
        ]);

        Category::create([
            'name' => 'ATK',
            'slug' => 'atk',
            'type' => 'inventory',
            'description' => 'Alat tulis kantor'
        ]);

        // Cash Categories
        Category::create([
            'name' => 'Iuran Member',
            'slug' => 'iuran-member',
            'type' => 'cash',
            'description' => 'Pemasukan dari iuran anggota'
        ]);

        Category::create([
            'name' => 'Donasi',
            'slug' => 'donasi',
            'type' => 'cash',
            'description' => 'Donasi dari pihak luar'
        ]);

        Category::create([
            'name' => 'Operasional',
            'slug' => 'operasional',
            'type' => 'cash',
            'description' => 'Biaya operasional'
        ]);

        Category::create([
            'name' => 'Pembelian Aset',
            'slug' => 'pembelian-aset',
            'type' => 'cash',
            'description' => 'Pembelian aset/inventaris'
        ]);

        // Units
        Unit::create(['name' => 'Unit', 'symbol' => 'pcs', 'description' => 'Pieces/Unit']);
        Unit::create(['name' => 'Box', 'symbol' => 'box', 'description' => 'Box']);
        Unit::create(['name' => 'Lusin', 'symbol' => 'dzn', 'description' => 'Dozen']);
        Unit::create(['name' => 'Set', 'symbol' => 'set', 'description' => 'Set']);
        Unit::create(['name' => 'Kilogram', 'symbol' => 'kg', 'description' => 'Kilogram']);
        Unit::create(['name' => 'Liter', 'symbol' => 'L', 'description' => 'Liter']);

        // Cash Accounts
        CashAccount::create([
            'name' => 'Kas Utama',
            'code' => 'KAS-001',
            'type' => 'cash',
            'balance' => 0,
            'description' => 'Kas untuk operasional harian'
        ]);

        CashAccount::create([
            'name' => 'Bank BCA',
            'code' => 'BNK-BCA-001',
            'type' => 'bank',
            'account_number' => '1234567890',
            'balance' => 0,
            'description' => 'Rekening utama organisasi'
        ]);
    }
}
```

Jalankan seeder:
```bash
php artisan db:seed --class=InitialDataSeeder
```

---

## AUTHENTICATION SETUP

### 1. Install Laravel Sanctum (sudah terinstall)

Pastikan di [config/sanctum.php](config/sanctum.php) stateful domain sudah dikonfigurasi untuk SPA:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort()
))),
```

### 2. Update [.env](.env)

```env
SANCTUM_STATEFUL_DOMAINS=localhost:8000,localhost
SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
```

### 3. Assign Role ke User

Setelah user register, assign role:

```php
$user = User::find(1);
$adminRole = Role::where('slug', 'admin')->first();
$user->roles()->attach($adminRole);
```

---

## MIDDLEWARE & PERMISSIONS

### Create RoleMiddleware

```bash
php artisan make:middleware RoleMiddleware
```

[app/Http/Middleware/RoleMiddleware.php](app/Http/Middleware/RoleMiddleware.php):

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}
```

Register middleware di [bootstrap/app.php](bootstrap/app.php):

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
```

Gunakan di routes:

```php
Route::middleware(['auth:sanctum', 'role:admin,super-admin'])->group(function () {
    // Protected routes
});
```

---

## FRONTEND DEVELOPMENT (Inertia + Vue)

### Struktur Component

```
resources/js/
├── Pages/
│   ├── Inventory/
│   │   ├── Items/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   ├── Edit.vue
│   │   │   └── Show.vue
│   │   └── Transactions/
│   │       ├── Index.vue
│   │       └── Create.vue
│   ├── Members/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   ├── Edit.vue
│   │   └── Show.vue
│   ├── Payments/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   └── Summary.vue
│   └── Cash/
│       ├── Accounts/
│       │   └── Index.vue
│       └── Transactions/
│           ├── Index.vue
│           ├── Create.vue
│           └── Summary.vue
└── Components/
    ├── Inventory/
    ├── Members/
    └── Cash/
```

### Contoh Vue Component untuk Inventory List

```vue
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const items = ref([]);
const loading = ref(false);
const filters = ref({
    search: '',
    category_id: null,
    status: 'available'
});

const fetchItems = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/inventory/items', {
            params: filters.value
        });
        items.value = response.data.data;
    } catch (error) {
        console.error('Error fetching items:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchItems();
});
</script>

<template>
    <div>
        <h1>Inventory Items</h1>

        <!-- Search & Filters -->
        <div class="filters">
            <input v-model="filters.search" @input="fetchItems" placeholder="Search..." />
            <select v-model="filters.status" @change="fetchItems">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
            </select>
        </div>

        <!-- Items Table -->
        <table v-if="!loading">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in items" :key="item.id">
                    <td>{{ item.code }}</td>
                    <td>{{ item.name }}</td>
                    <td>{{ item.category.name }}</td>
                    <td>{{ item.current_stock }} {{ item.unit.symbol }}</td>
                    <td>
                        <span :class="['badge', item.status]">
                            {{ item.status }}
                        </span>
                    </td>
                    <td>
                        <a :href="`/inventory/items/${item.id}`">View</a>
                        <a :href="`/inventory/items/${item.id}/edit`">Edit</a>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-else>Loading...</div>
    </div>
</template>
```

---

## TESTING

### Setup Testing

```bash
php artisan test
```

### Contoh Test untuk Inventory

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\InventoryItem;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_inventory_item()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'inventory']);
        $unit = Unit::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/inventory/items', [
            'code' => 'TEST-001',
            'name' => 'Test Item',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 10,
            'minimum_stock' => 5,
            'purchase_price' => 100000,
            'selling_price' => 150000,
            'status' => 'available'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('inventory_items', [
            'code' => 'TEST-001',
            'name' => 'Test Item'
        ]);
    }

    public function test_cannot_create_transaction_with_insufficient_stock()
    {
        $user = User::factory()->create();
        $item = InventoryItem::factory()->create(['current_stock' => 5]);

        $response = $this->actingAs($user)->postJson('/api/inventory/transactions', [
            'inventory_item_id' => $item->id,
            'type' => 'out',
            'quantity' => 10,
            'transaction_date' => now()->format('Y-m-d')
        ]);

        $response->assertStatus(400);
    }
}
```

---

## LAPORAN & EXPORT

### 1. Install Laravel Excel (Opsional)

```bash
composer require maatwebsite/excel
```

### 2. Contoh Export Controller

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberPayment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function paymentReport(Request $request)
    {
        $period = $request->input('period', now()->format('Y-m'));

        $payments = MemberPayment::with(['member', 'recorder'])
            ->period($period)
            ->get();

        // Return untuk download atau view
        return response()->json([
            'period' => $period,
            'payments' => $payments,
            'summary' => [
                'total_paid' => $payments->where('status', 'paid')->sum('amount'),
                'total_unpaid' => $payments->where('status', 'unpaid')->count(),
            ]
        ]);
    }

    public function inventoryReport(Request $request)
    {
        $items = InventoryItem::with(['category', 'unit', 'transactions'])
            ->get();

        return response()->json([
            'items' => $items,
            'summary' => [
                'total_items' => $items->count(),
                'low_stock_items' => $items->filter->isLowStock()->count(),
                'total_value' => $items->sum(fn($item) => $item->current_stock * $item->purchase_price)
            ]
        ]);
    }
}
```

Tambahkan routes:

```php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('reports/payments', [ReportController::class, 'paymentReport']);
    Route::get('reports/inventory', [ReportController::class, 'inventoryReport']);
});
```

---

## NOTIFIKASI (Future Enhancement)

### Setup Laravel Notifications

```bash
php artisan notifications:table
php artisan migrate
```

### Contoh Notification untuk Low Stock

```php
<?php

namespace App\Notifications;

use App\Models\InventoryItem;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    public function __construct(public InventoryItem $item) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => "Stock rendah: {$this->item->name}",
            'item_id' => $this->item->id,
            'current_stock' => $this->item->current_stock,
            'minimum_stock' => $this->item->minimum_stock,
        ];
    }
}
```

Trigger notification setelah transaksi:

```php
// Di InventoryTransactionController
if ($item->isLowStock()) {
    $admins = User::whereHas('roles', function($q) {
        $q->where('slug', 'admin');
    })->get();

    Notification::send($admins, new LowStockNotification($item));
}
```

---

## DEPLOYMENT CHECKLIST

### 1. Environment Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

### 2. Optimize Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 3. Build Frontend Assets

```bash
npm run build
```

### 4. Setup Queue Worker (Opsional)

```bash
php artisan queue:work --daemon
```

### 5. Setup Scheduled Tasks

Tambahkan cron job:
```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## SECURITY BEST PRACTICES

1. **Always validate input** - Gunakan Form Requests
2. **Use CSRF protection** - Sudah built-in di Laravel
3. **Implement rate limiting** - Untuk API endpoints
4. **Encrypt sensitive data** - Password, secrets
5. **Use transactions** - Untuk operasi multi-step
6. **Implement logging** - Track semua transaksi penting
7. **Regular backups** - Database dan files

---

## SCALING CONSIDERATIONS

### 1. Database Indexing
- Sudah ada indexes di migrations
- Monitor query performance dengan Laravel Telescope

### 2. Caching
```php
// Cache kategori (jarang berubah)
$categories = Cache::remember('categories', 3600, function() {
    return Category::active()->get();
});
```

### 3. Queue Jobs
```php
// Untuk operasi berat seperti export
ProcessReportExport::dispatch($userId, $period);
```

### 4. Load Balancing
- Gunakan Redis untuk session storage
- CDN untuk static assets

---

## MAINTENANCE

### Daily Tasks
- Monitor error logs: `storage/logs/laravel.log`
- Check queue status: `php artisan queue:failed`

### Weekly Tasks
- Review low stock items
- Backup database

### Monthly Tasks
- Audit user activities
- Review and archive old data
- Performance optimization

---

## SUPPORT & RESOURCES

### Laravel Documentation
- https://laravel.com/docs

### Inertia.js Documentation
- https://inertiajs.com

### Vue.js Documentation
- https://vuejs.org

### API Testing
- Postman Collection: Import dari API_DOCUMENTATION.md
- Insomnia: Support OpenAPI spec

---

## FUTURE ENHANCEMENTS

1. **Role & Permission System** - Granular permissions
2. **Audit Trail** - Track semua changes
3. **Dashboard Analytics** - Charts dan graphs
4. **Mobile App** - Flutter/React Native
5. **Barcode/QR Scanner** - Untuk inventory tracking
6. **Multi-tenant** - Support multiple organizations
7. **Advanced Reporting** - PDF/Excel export dengan template
8. **Notification System** - Email, SMS, Push notifications
9. **Document Management** - Upload dan attach files
10. **API Versioning** - v1, v2, dst.
