<?php

namespace BZpoultryfarm;

use Illuminate\Database\Eloquent\Model;

class order_list extends Model
{

	public $timestamps = false;

    public function order()
    {
    	return $this->hasOne('BZpoultryfarm\order','order_id');
    }
}
