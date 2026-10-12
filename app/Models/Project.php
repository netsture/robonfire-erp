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

    /**
     * Get counts of existing child records associated with this project.
     *
     * @return array<string, int>
     */
    public function getChildRelationsCount(): array
    {
        $counts = [
            'expense' => $this->expenses()->count(),
            'purchase' => Purchase::where('firm_id', $this->firm_id)->where('project_name', $this->project_name)->count(),
            'returnable material' => ReturnableMaterial::where('firm_id', $this->firm_id)->where('project_name', $this->project_name)->count(),
        ];

        return array_filter($counts, fn ($count) => $count > 0);
    }

    /**
     * Check if project has any child records.
     */
    public function hasChildRecords(): bool
    {
        return !empty($this->getChildRelationsCount());
    }
}
