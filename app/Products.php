<?php

namespace BZpoultryfarm;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = array(
    	'name',
    	'price',
    	'stocks',
    	'added_by'
    );
}