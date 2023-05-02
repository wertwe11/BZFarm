<?php

namespace BZpoultryfarm;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    public $timestamps = false;

    public function users()
    {
    	return $this->belongsTo('BZpoultryfarm\Users','user_id');
    }

    public function customers()
    {
    	return $this->belongsTo('BZpoultryfarm\Customers','email');
    }

    public function order_list()
    {
    	return $this->hasMany('BZpoultryfarm\Order_list','order_list_id');
    }

    protected $fillable = array(
        'id',
        'order_id',
        'total_cost',
        'cust_email',
        'handled_by',
        'user_id',
        'order_placed',
        'status',
        'trans_date',
    );

}
