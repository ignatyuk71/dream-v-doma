<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeValueTranslation extends Model
{
    protected $fillable = [
        'product_attribute_value_id',
        'locale',
        'value',
        'slug',
    ];

    public function attributeValue()
    {
        return $this->belongsTo(ProductAttributeValue::class, 'product_attribute_value_id');
    }
}
