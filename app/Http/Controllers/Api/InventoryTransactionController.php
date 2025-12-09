<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{DB, Validator};
use App\Models\{InventoryItem, InventoryTransaction};
use App\Traits\ApiPaginationTrait;
use Pest\Support\Str;

class InventoryTransactionController extends BaseApiController
{
    use ApiPaginationTrait;

    public function index(Request $request): JsonResponse
    {
        $query = InventoryTransaction::with(['inventoryItem', 'recorder']);

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by item
        if ($request->has('inventory_item_id')) {
            $query->where('inventory_item_id', $request->inventory_item_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $query->latest('transaction_date');

        return $this->paginatedResponse($query, $request, 'Inventory transactions retrieved successfully');
    }

    private function generateReferenceNumber(int $length = 6): string
    {
        $randomString = strtoupper(Str::random($length));
        return 'INV-' . now()->format('Ymd') . '-' . $randomString;
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'inventory_item_id' => 'required|exists:inventory_items,uuid',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'price_per_unit' => 'nullable|numeric|min:0',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            DB::beginTransaction();

            // Resolve UUID to internal ID
            $item = InventoryItem::where('uuid', $request->inventory_item_id)->first();
            if (!$item) {
                return $this->resourceNotFound('inventory item', $request->inventory_item_id, 'inventory_item_id');
            }

            // Check if stock is sufficient for 'out' transactions
            if ($request->type === 'out' && $item->current_stock < $request->quantity) {
                return $this->errorResponse('Insufficient stock', 400, [
                    'available_stock' => $item->current_stock
                ]);
            }

            $stockBefore = $item->current_stock;

            // Calculate new stock
            $stockAfter = match ($request->type) {
                'in' => $stockBefore + $request->quantity,
                'out' => $stockBefore - $request->quantity,
                'adjustment' => $request->quantity, // For adjustment, quantity is the new total
            };

            // Generate transaction code
            $transactionCode = 'INV-' . strtoupper($request->type) . '-' . now()->format('Ymd') . '-' . str_pad(InventoryTransaction::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate total price
            $pricePerUnit = $request->price_per_unit ?? $item->purchase_price;
            $totalPrice = $pricePerUnit * $request->quantity;

            $referenceNumber = $this->generateReferenceNumber();

            // Create transaction
            $transaction = InventoryTransaction::create([
                'transaction_code' => $transactionCode,
                'inventory_item_id' => $item->id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'price_per_unit' => $pricePerUnit,
                'total_price' => $totalPrice,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'transaction_date' => $request->transaction_date,
                'reference_number' => $referenceNumber,
                'notes' => $request->notes,
                'recorded_by' => auth()->check() ? auth()->id() : 1,
            ]);

            // Update item stock
            $item->update(['current_stock' => $stockAfter]);

            DB::commit();

            $transaction->load(['inventoryItem', 'recorder']);

            return $this->successResponse($transaction, 'Transaction created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Transaction failed: ' . $e->getMessage(), 500);
        }
    }

    public function show(InventoryTransaction $inventoryTransaction): JsonResponse
    {
        $inventoryTransaction->load(['inventoryItem', 'recorder']);
        return $this->successResponse($inventoryTransaction);
    }

    public function history(InventoryItem $inventoryItem, Request $request): JsonResponse
    {
        $query = $inventoryItem->transactions()
            ->with('recorder')
            ->latest('transaction_date');

        return $this->paginatedResponse($query, $request, 'Transaction history retrieved successfully');
    }
}
