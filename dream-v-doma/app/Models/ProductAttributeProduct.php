<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeProduct extends Model
{
    protected $table = 'product_attribute_product';

    protected $fillable = [
        'product_id',
        'product_attribute_value_id',
    ];

    // Зв'язок з товаром
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Зв'язок з value (значенням характеристики)
    public function attributeValue()
    {
        return $this->belongsTo(ProductAttributeValue::class, 'product_attribute_value_id');
    }
}
