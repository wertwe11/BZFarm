<?php

namespace BZpoultryfarm;

use Illuminate\Database\Eloquent\Model;

class UsersArchives extends Model
{
    public function user()
    {
    	return $this->belongsTo('BZpoultryfarm\User','user_id');
    }
}
