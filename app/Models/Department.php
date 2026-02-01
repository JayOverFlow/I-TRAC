<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments_tbl'; // The table name
    protected $primaryKey = 'dep_id'; // The PR of the table
    public $incrementing = true; // Optional cause Laravel automatically set it
    protected $keyType = 'int'; // Tell the Laravel the datatype of the PK

    // Mass assignment fields
    protected $fillable = [
        'dep_name',
        'dep_type',
    ];

    // METHODS
}
