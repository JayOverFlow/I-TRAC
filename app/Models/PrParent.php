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
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // One PR has many items
    public function prItems()
    {
        return $this->hasMany(PrItem::class, 'pr_id_fk', 'pr_id');
    }

    // A PR can have multiple tasks (subordinate's "Purchase Request" + Head's "PR Review")
    public function tasks()
    {
        return $this->hasMany(Task::class, 'pr_id_fk', 'pr_id');
    }

    // The department this PR belongs to
    public function department()
    {
        return $this->belongsTo(Department::class, 'pr_department', 'dep_id');
    }

    // The user who requested this PR
    public function requestor()
    {
        return $this->belongsTo(User::class, 'pr_name_of_requestor', 'user_id');
    }

    // The user who saved this PR
    public function savedBy()
    {
        return $this->belongsTo(User::class, 'saved_by_user_id_fk', 'user_id');
    }
}
