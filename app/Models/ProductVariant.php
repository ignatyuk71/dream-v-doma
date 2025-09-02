<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'type',
        'size',
        'color',
        'quantity',
        'price_override',
        'old_price',
        'variant_sku',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'old_price'      => 'decimal:2',
        'quantity'       => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function setVariantSkuAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['variant_sku'] = null;
            return;
        }

        $v = trim((string) $value);
        $this->attributes['variant_sku'] = $v === '' ? null : mb_strtoupper($v);
    }

    /**
     * Автогенерація variant_sku у форматі PRODUCT.SKU-#### (4 цифри)
     */
    protected static function booted()
    {
        static::creating(function (ProductVariant $variant) {
            if (blank($variant->variant_sku)) {
                // шукаємо sku продукту
                $productSku = null;

                if ($variant->relationLoaded('product') && $variant->product) {
                    $productSku = $variant->product->sku;
                }

                if (!$productSku && $variant->product_id) {
                    $productSku = Product::whereKey($variant->product_id)->value('sku');
                }

                $prefix = $productSku ? Str::upper(trim($productSku)) : 'PRD' . $variant->product_id;

                // генеруємо 4 цифри
                $tries = 30;
                for ($i = 0; $i < $tries; $i++) {
                    $rand = random_int(0, 9999); // 0000..9999
                    $candidate = sprintf('%s-%04d', $prefix, $rand);

                    if (!static::where('variant_sku', $candidate)->exists()) {
                        $variant->variant_sku = $candidate;
                        break;
                    }
                }
            }
        });
    }
}
