<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments_tbl'; // The table name
    protected $primaryKey = 'dep_id'; // The PR of the table
    public $incrementing = true; // Optional cause Laravel automatically set it
    protected $keyType = 'int'; // Tell the Laravel the datatype of the PK
    public $timestamps = false;

    // Mass assignment fields
    protected $fillable = [
        'dep_name',
        'dep_acronym',
        'parent_dep_id',
    ];

    // METHODS

    /**
     * Relationship: Parent Department/Office
     */
    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_dep_id', 'dep_id');
    }

    /**
     * Relationship: Sub-departments/Offices
     */
    public function children()
    {
        return $this->hasMany(Department::class, 'parent_dep_id', 'dep_id');
    }

    /**
     * Relationship: RIS slips associated with this department
     */
    public function risSlips()
    {
        return $this->hasMany(Ris::class, 'ris_office', 'dep_name');
    }

    /**
     * Relationship: Users belonging to this department
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_departments_tbl', 'department_id_fk', 'user_id_fk');
    }
}
