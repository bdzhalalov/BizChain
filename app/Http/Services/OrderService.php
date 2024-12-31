<?php

namespace App\Http\Services;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected ClientService $clientService;

    public function __construct()
    {
        $this->clientService = new ClientService();
    }

    /**
     * @param array $data
     * @return OrderResource
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws \Throwable
     */
    public function createOrderForClient(array $data): OrderResource
    {
        Log::debug(
            'Start creating order for client',
            [
                'client_id' => $data['client_id']
            ]
        );

        try {
            DB::beginTransaction();

            // check client existence
            $this->clientService->getClientById($data['client_id']);

            // create new order for client
            $order = Order::create(['client_id' => $data['client_id']]);

            // get the oldest batches for products and storage id according batches
            $productBatches = DB::table('batch_product')
                ->join('batches', 'batch_product.batch_id', '=', 'batches.id')
                ->join('product_storage', 'product_storage.product_id', '=', 'batch_product.product_id')
                ->select(
                    'batch_product.product_id as product_id',
                    'batch_product.batch_id as batch_id',
                    'batch_product.quantity as batch_qty',
                    'product_storage.storage_id as storage_id',
                    'product_storage.quantity as storage_qty'
                )
                ->whereIn('batch_product.product_id', collect($data['products'])->pluck('id'))
                ->where('product_storage.quantity', '>', 0)
                ->orderBy('batches.created_at', 'asc')
                ->get()
                ->keyBy('product_id');

            //prepare data for inserting to the db
            $orderProducts = [];
            $cases = [];
            $ids = [];
            $storageIds = [];

            foreach ($data['products'] as $product) {
                //check that ordered quantity smaller or equal to quantity in storage
                if ($product['qty'] > $productBatches[$product['id']]->storage_qty) {
                    throw BadRequestException::getInstance(
                        "The quantity of returned product with id {$product['id']} must not exceed the current quantity in storage"
                    );
                }

                $storageId = $productBatches[$product['id']]->storage_id;

                // prepare data for order_product table
                $orderProducts[] = [
                    'order_id' => $order['id'],
                    'product_id' => $product['id'],
                    'storage_id' => $storageId,
                    'quantity' => $product['qty'],
                    'created_at' => now(),
                ];

                //prepare final product quantity for decrement from storage
                $currentQuantity = $productBatches[$product['id']]->storage_qty;
                $finalQuantity = $currentQuantity - $product['qty'];

                $cases[] = "WHEN product_id = {$product['id']} AND storage_id = $storageId THEN $finalQuantity";
                $ids[] = $product['id'];
                $storageIds[] = $storageId;
            }

            // insert order products to order_product table
            DB::table('order_product')->insert($orderProducts);

            // update product quantity in product_storage table
            $cases = implode(' ', $cases);
            $ids = implode(',', $ids);
            $storageIds = implode(',', $storageIds);

            DB::statement("
                UPDATE product_storage
                SET
                    quantity = CASE
                        $cases
                    END,
                updated_at = NOW()
                WHERE storage_id IN ($storageIds) AND product_id IN ($ids)
            ");

            DB::commit();
            return new OrderResource($order);
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
