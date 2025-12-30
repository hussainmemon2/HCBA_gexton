<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryItemAttachment extends Model
{
    protected $fillable=[
        'library_item_id',
        'filename'
    ];
    //
}
