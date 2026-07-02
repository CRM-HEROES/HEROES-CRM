<?php

namespace App\Observers;

use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageObserver
{
    /**
     * Handle the ProductImage "creating" event.
     */
    public function creating(ProductImage $image): void
    {
        $image->order = $this->getOrder($image);
    }
    
    /**
     * Handle the ProductImage "updating" event.
     */
    public function updating(ProductImage $image): void
    {
        $this->deleteFile($image);
        $this->updateOrder($image);
    }
    
    /**
     * Handle the ProductImage "deleting" event.
     */
    public function deleting(ProductImage $image): void
    {
        $this->deleteFile($image);
    }
    
    /**
     * Handle the ProductImage "deleted" event.
     */
    public function deleted(ProductImage $image): void
    {
        $this->deleteOrder($image);
    }

    /**
     * Delete image
     *
     * @param  \App\Models\ProductImage  $image
     * @return void
     */
    protected function deleteUpdatedFile(ProductImage $image)
    {
        if (!$image->isDirty('path')) {
            return;
        }

        $disk = Storage::disk('products');
        if (!$disk->exists($image->getOriginal('path'))) {
            return;
        }

        $disk->delete($image->getOriginal('path'));
    }
    
    /**
     * Delete image
     *
     * @param  \App\Models\ProductImage  $image
     * @return void
     */
    protected function deleteFile(ProductImage $image)
    {
        $disk = Storage::disk('products');
        if (!$disk->exists($image->path)) {
            return;
        }

        $disk->delete($image->path);
    }
    
    /**
     * Get order
     *
     * @param  \App\Models\ProductImage  $image
     * @return void
     */
    protected function getOrder(ProductImage $image)
    {
        return ProductImage
            ::where('product_id', $image->product_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\ProductImage  $image
     * @return void
     */
    protected function updateOrder(ProductImage $image)
    {
        if (!$image->isDirty('order')) {
            return;
        }

        $count = DB::table('product_images')
            ->where('product_id', $image->product_id)
            ->count();

        $image->order = max(1, min($image->order, $count));

        $oldOrder = $image->getOriginal('order');
        $newOrder = $image->order;

        if ($oldOrder < $newOrder) {
            DB::table('product_images')
                ->where('product_id', $image->product_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('product_images')
                ->where('product_id', $image->product_id)
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
     * @param  \App\Models\ProductImage  $image
     * @return void
     */
    protected function deleteOrder(ProductImage $image)
    {
        DB::table('product_images')
            ->where('product_id', $image->product_id)
            ->where('order', '>=', $image->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
