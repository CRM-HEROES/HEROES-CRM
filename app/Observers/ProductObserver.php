<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductObserver
{
    /**
     * Handle the Product "creating" event.
     */
    public function creating(Product $product): void
    {
        $product->order = $this->getOrder($product);
    }

    /**
     * Handle the Product "updating" event.
     */
    public function updating(Product $product): void
    {
        $this->updateOrder($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->deleteOrder($product);
    }

    /**
     * Handle the Product "deleting" event.
     */
    public function deleting(Product $product): void
    {
        $this->deleteImages($product);
    }

    /**
     * Get order
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    protected function getOrder(Product $product)
    {
        return Product
            ::where('project_id', $product->project_id)
            ->count() + 1;
    }
    
    /**
     * Delete product
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    protected function deleteImages(Product $product)
    {
        foreach ($product->images as $image) {
            $image->delete();
        } 
    }

    /**
     * Update order
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    protected function updateOrder(Product $product)
    {
        if (!$product->isDirty('order')) {
            return;
        }

        $count = DB::table('products')
            ->where('project_id', $product->project_id)
            ->count();

        $product->order = max(1, min($product->order, $count));

        $oldOrder = $product->getOriginal('order');
        $newOrder = $product->order;

        if ($oldOrder < $newOrder) {
            DB::table('products')
                ->where('project_id', $product->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('products')
                ->where('project_id', $product->project_id)
                ->where('order', '>=', $newOrder)
                ->where('order', '<', $oldOrder)
                ->update([
                    'order' => DB::raw('`order` + 1')
                ]);
        }
    }

    /**
     * Delete order
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    protected function deleteOrder(Product $product)
    {
        DB::table('products')
            ->where('project_id', $product->project_id)
            ->where('order', '>=', $product->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
