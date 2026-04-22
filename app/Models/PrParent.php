<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrParent extends Model
{
    protected $table = 'pr_tbl';
    public $timestamps = false; // pr_tbl has no updated_at column
    protected $primaryKey = 'pr_id';

    protected $fillable = [
        'pr_section',
        'pr_department',
        'pr_no',
        'pr_date',
        'pr_purpose',
        'pr_name_of_requestor',
        'pr_designation',
        'pr_approved_by',
        'pr_approved_by_designation',
        'saved_by_user_id_fk',
        'pr_unique_code',
        'pr_status',
    ];

    // One PR has many items
    public function prItems()
    {
        return $this->hasMany(PrItem::class, 'pr_id_fk', 'pr_id');
    }

    // The task that owns this PR
    public function task()
    {
        return $this->hasOne(Task::class, 'pr_id_fk', 'pr_id');
    }
}
