<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

  protected $fillable = [

             'id','full_picture','message','created_time','created_at','updated_at'


  ];
}
