<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Customer;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PurchaseService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Validate products and inventory before purchase
     * 
     * @param array $items Array of items with product_id and quantity
     * @return array Validation result with validated products and any errors
     * @throws Exception
     */
    public function validatePurchaseItems(array $items): array
    {
        $validatedItems = [];
        $errors = [];
        $warnings = [];

        if (empty($items)) {
            throw new Exception('Purchase items cannot be empty');
        }

        // Check for duplicate product IDs
        $productIds = array_column($items, 'product_id');
        $duplicates = array_diff_assoc($productIds, array_unique($productIds));
        if (!empty($duplicates)) {
            throw new Exception('Duplicate products found in purchase items');
        }

        foreach ($items as $index => $item) {
            try {
                // Validate item structure
                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                    $errors[] = [
                        'index' => $index,
                        'product_id' => $item['product_id'] ?? null,
                        'error' => 'Missing required fields: product_id and quantity are required'
                    ];
                    continue;
                }

                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                // Validate quantity
                if (!is_numeric($quantity) || $quantity <= 0 || !is_int($quantity + 0)) {
                    $errors[] = [
                        'index' => $index,
                        'product_id' => $productId,
                        'error' => "Invalid quantity: must be a positive integer, got '{$quantity}'"
                    ];
                    continue;
                }

                $quantity = (int) $quantity;

                // Check if product exists
                $product = Product::find($productId);
                if (!$product) {
                    $errors[] = [
                        'index' => $index,
                        'product_id' => $productId,
                        'error' => "Product not found with ID: {$productId}"
                    ];
                    continue;
                }

                // Check if product is active
                if (!$product->is_active) {
                    $errors[] = [
                        'index' => $index,
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'error' => "Product '{$product->name}' is not available (inactive)"
                    ];
                    continue;
                }

                // Check stock availability
                $availableStock = $product->stock_quantity;
                if ($availableStock < $quantity) {
                    $errors[] = [
                        'index' => $index,
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'error' => "Insufficient stock for '{$product->name}'. Available: {$availableStock}, Requested: {$quantity}",
                        'available_stock' => $availableStock,
                        'requested_quantity' => $quantity
                    ];
                    continue;
                }

                // Check if product is out of stock
                if ($availableStock <= 0) {
                    $errors[] = [
                        'index' => $index,
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'error' => "Product '{$product->name}' is out of stock",
                        'available_stock' => 0
                    ];
                    continue;
                }

                // Check for low stock warning
                if ($availableStock <= 10 && $availableStock > 0) {
                    $warnings[] = [
                        'index' => $index,
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'warning' => "Product '{$product->name}' has low stock ({$availableStock} remaining)",
                        'available_stock' => $availableStock
                    ];
                }

                // Validate price exists
                if (!isset($product->price) || $product->price <= 0) {
                    $errors[] = [
                        'index' => $index,
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'error' => "Product '{$product->name}' has invalid price"
                    ];
                    continue;
                }

                // Add validated item
                $validatedItems[] = [
                    'product_id' => $product->id,
                    'product' => $product,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $product->price * $quantity,
                    'available_stock' => $availableStock,
                ];

            } catch (Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'product_id' => $item['product_id'] ?? null,
                    'error' => $e->getMessage()
                ];
            }
        }

        // If there are errors, throw exception with details
        if (!empty($errors)) {
            $errorMessages = array_column($errors, 'error');
            throw new Exception('Purchase validation failed: ' . implode('; ', $errorMessages));
        }

        return [
            'success' => true,
            'validated_items' => $validatedItems,
            'warnings' => $warnings,
            'total_items' => count($validatedItems),
            'total_quantity' => array_sum(array_column($validatedItems, 'quantity')),
            'total_amount' => array_sum(array_column($validatedItems, 'total')),
        ];
    }

    /**
     * Validate purchase items with database locks for concurrent requests
     * 
     * @param array $items Array of items with product_id and quantity
     * @return array Validation result with validated products
     * @throws Exception
     */
    public function validatePurchaseItemsWithLock(array $items): array
    {
        DB::beginTransaction();
        
        try {
            $validatedItems = [];
            $errors = [];
            $warnings = [];

            if (empty($items)) {
                throw new Exception('Purchase items cannot be empty');
            }

            // Check for duplicate product IDs
            $productIds = array_column($items, 'product_id');
            $duplicates = array_diff_assoc($productIds, array_unique($productIds));
            if (!empty($duplicates)) {
                throw new Exception('Duplicate products found in purchase items');
            }

            // Lock all products for update to prevent race conditions
            $productIds = array_unique($productIds);
            $products = Product::lockForUpdate()
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            foreach ($items as $index => $item) {
                try {
                    // Validate item structure
                    if (!isset($item['product_id']) || !isset($item['quantity'])) {
                        $errors[] = [
                            'index' => $index,
                            'product_id' => $item['product_id'] ?? null,
                            'error' => 'Missing required fields: product_id and quantity are required'
                        ];
                        continue;
                    }

                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];

                    // Validate quantity
                    if (!is_numeric($quantity) || $quantity <= 0 || !is_int($quantity + 0)) {
                        $errors[] = [
                            'index' => $index,
                            'product_id' => $productId,
                            'error' => "Invalid quantity: must be a positive integer, got '{$quantity}'"
                        ];
                        continue;
                    }

                    $quantity = (int) $quantity;

                    // Check if product exists
                    if (!$products->has($productId)) {
                        $errors[] = [
                            'index' => $index,
                            'product_id' => $productId,
                            'error' => "Product not found with ID: {$productId}"
                        ];
                        continue;
                    }

                    $product = $products->get($productId);

                    // Check if product is active
                    if (!$product->is_active) {
                        $errors[] = [
                            'index' => $index,
                            'product_id' => $productId,
                            'product_name' => $product->name,
                            'error' => "Product '{$product->name}' is not available (inactive)"
                        ];
                        continue;
                    }

                    // Check stock availability (using locked product)
                    $availableStock = $product->stock_quantity;
                    if ($availableStock < $quantity) {
                        $errors[] = [
                            'index' => $index,
                            'product_id' => $productId,
                            'product_name' => $product->name,
                            'error' => "Insufficient stock for '{$product->name}'. Available: {$availableStock}, Requested: {$quantity}",
                            'available_stock' => $availableStock,
                            'requested_quantity' => $quantity
                        ];
                        continue;
                    }

                    // Check if product is out of stock
                    if ($availableStock <= 0) {
                        $errors[] = [
                            'index' => $index,
                            'product_id' => $productId,
                            'product_name' => $product->name,
                            'error' => "Product '{$product->name}' is out of stock",
                            'available_stock' => 0
                        ];
                        continue;
                    }

                    // Check for low stock warning
                    if ($availableStock <= 10 && $availableStock > 0) {
                        $warnings[] = [
                            'index' => $index,
                            'product_id' => $productId,
                            'product_name' => $product->name,
                            'warning' => "Product '{$product->name}' has low stock ({$availableStock} remaining)",
                            'available_stock' => $availableStock
                        ];
                    }

                    // Validate price exists
                    if (!isset($product->price) || $product->price <= 0) {
                        $errors[] = [
                            'index' => $index,
                            'product_id' => $productId,
                            'product_name' => $product->name,
                            'error' => "Product '{$product->name}' has invalid price"
                        ];
                        continue;
                    }

                    // Add validated item
                    $validatedItems[] = [
                        'product_id' => $product->id,
                        'product' => $product,
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'total' => $product->price * $quantity,
                        'available_stock' => $availableStock,
                    ];

                } catch (Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'product_id' => $item['product_id'] ?? null,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // If there are errors, rollback and throw exception
            if (!empty($errors)) {
                DB::rollBack();
                $errorMessages = array_column($errors, 'error');
                throw new Exception('Purchase validation failed: ' . implode('; ', $errorMessages));
            }

            DB::commit();

            return [
                'success' => true,
                'validated_items' => $validatedItems,
                'warnings' => $warnings,
                'total_items' => count($validatedItems),
                'total_quantity' => array_sum(array_column($validatedItems, 'quantity')),
                'total_amount' => array_sum(array_column($validatedItems, 'total')),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Purchase validation with lock failed', [
                'items' => $items,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check product availability without creating order
     * 
     * @param array $items Array of items with product_id and quantity
     * @return array Availability check result
     */
    public function checkAvailability(array $items): array
    {
        try {
            $result = $this->validatePurchaseItems($items);
            
            return [
                'success' => true,
                'available' => true,
                'items' => array_map(function ($item) {
                    return [
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product']->name,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['total'],
                        'available_stock' => $item['available_stock'],
                    ];
                }, $result['validated_items']),
                'warnings' => $result['warnings'],
                'total_amount' => $result['total_amount'],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'available' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate customer can make purchase
     * 
     * @param int $customerId
     * @return array
     * @throws Exception
     */
    public function validateCustomer(int $customerId): array
    {
        $customer = Customer::find($customerId);
        
        if (!$customer) {
            throw new Exception("Customer not found with ID: {$customerId}");
        }

        // Check if customer is banned
        if ($customer->isBanned()) {
            throw new Exception("Customer account is banned. Reason: " . ($customer->ban_reason ?? 'No reason provided'));
        }

        // Check if customer is suspended
        if ($customer->isSuspended()) {
            throw new Exception("Customer account is suspended. Reason: " . ($customer->suspend_reason ?? 'No reason provided'));
        }

        return [
            'success' => true,
            'customer' => $customer,
        ];
    }

    /**
     * Get purchase summary before checkout
     * 
     * @param array $items
     * @param int|null $customerId
     * @return array
     */
    public function getPurchaseSummary(array $items, ?int $customerId = null): array
    {
        try {
            // Validate customer if provided
            if ($customerId) {
                $this->validateCustomer($customerId);
            }

            // Validate items
            $validation = $this->validatePurchaseItems($items);

            return [
                'success' => true,
                'summary' => [
                    'total_items' => $validation['total_items'],
                    'total_quantity' => $validation['total_quantity'],
                    'total_amount' => $validation['total_amount'],
                    'items' => array_map(function ($item) {
                        return [
                            'product_id' => $item['product_id'],
                            'product_name' => $item['product']->name,
                            'product_sku' => $item['product']->sku,
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['price'],
                            'item_total' => $item['total'],
                            'available_stock' => $item['available_stock'],
                        ];
                    }, $validation['validated_items']),
                ],
                'warnings' => $validation['warnings'],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

