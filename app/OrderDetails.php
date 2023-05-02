<?php

namespace BZpoultryfarm;

use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    protected $fillable = array(
        'id',
        'order_id',
        'product_name',
        'quantity',
        'created_at',
        'updated_at',
    );
}
