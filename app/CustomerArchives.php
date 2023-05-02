<?php

namespace BZpoultryfarm;

use Illuminate\Database\Eloquent\Model;

class CustomerArchives extends Model
{
    public function customer()
    {
    	return $this->belongsTo('BZpoultryfarm\customers','cust_id');
    }
}
