<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'project_name',
        'po_number',
        'po_date',
        'po_amount',
    ];

    protected $casts = [
        'po_date' => 'date',
        'po_amount' => 'decimal:2',
    ];

    public function getNameAttribute()
    {
        return $this->attributes['project_name'] ?? null;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['project_name'] = $value;
    }

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function expenses()
    {
        return $this->hasMany(ProjectExpense::class)->latest('expense_date');
    }

    public function getTotalExpensesAttribute()
    {
        return (float) $this->expenses()->sum('amount');
    }
}
