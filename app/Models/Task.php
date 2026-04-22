<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks_tbl';
    protected $primaryKey = 'task_id';
    public $timestamps = false;

    protected $fillable = [
        'assigned_by',
        'assigned_to',
        'task_description',
        'created_at',
        'pr_id_fk',
        'task_type',
        'is_deleted',
        'task_status',
    ];

    // The head user who created the task
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by', 'user_id');
    }

    // The subordinate user who received the task
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    // The PR linked to this task
    public function purchaseRequest()
    {
        return $this->belongsTo(PrParent::class, 'pr_id_fk', 'pr_id');
    }

    // All APP items dedicated to this task
    public function appItems()
    {
        return $this->belongsToMany(
            AppItem::class,
            'task_items_tbl',
            'task_id_fk',
            'app_item_id_fk',
            'task_id',
            'app_item_id'
        );
    }
}
