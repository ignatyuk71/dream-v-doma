<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingSetting extends Model
{
    protected $table = 'tracking_settings';

    protected $fillable = [
        'pixel_enabled','pixel_id','default_currency','exclude_admin',
        'send_view_content','send_add_to_cart','send_initiate_checkout','send_purchase','send_lead',
        'require_consent',
        'capi_enabled','capi_token','capi_test_code','capi_api_version',
    ];

    protected $casts = [
        'pixel_enabled' => 'boolean',
        'exclude_admin' => 'boolean',
        'send_view_content' => 'boolean',
        'send_add_to_cart' => 'boolean',
        'send_initiate_checkout' => 'boolean',
        'send_purchase' => 'boolean',
        'send_lead' => 'boolean',
        'require_consent' => 'boolean',
        'capi_enabled' => 'boolean',
    ];
}
