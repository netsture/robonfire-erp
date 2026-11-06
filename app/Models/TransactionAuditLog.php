<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransactionAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'user_id',
        'module',          // 'purchase', 'sale', 'returnable'
        'action',          // 'create', 'update', 'delete'
        'record_id',
        'reference_no',    // invoice_number or return_number
        'project_name',
        'party_name',      // Supplier or Customer name
        'party_type',      // 'Supplier' or 'Customer'
        'items_count',
        'products_summary',
        'grand_total',
        'paid_amount',
        'payment_status',
        'description',
        'changes',
        'ip_address',
    ];

    protected $casts = [
        'changes'     => 'array',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'items_count' => 'integer',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a transaction audit log entry (Purchase, Sale, Returnable).
     *
     * @param string $module 'purchase', 'sale', 'returnable'
     * @param string $action 'create', 'update', 'delete'
     * @param mixed  $record Model instance
     * @param array|null $before
     * @param array|null $after
     * @param string|null $customDesc
     * @return self|null
     */
    public static function record(string $module, string $action, $record, ?array $before = null, ?array $after = null, ?string $customDesc = null): ?self
    {
        try {
            $user = Auth::user();
            $userId = $user?->id;
            $firmId = $record->firm_id ?? $user?->firm_id;

            $referenceNo = $record->invoice_number ?? $record->return_number ?? 'N/A';
            $projectName = $record->project_name ?? null;

            $partyName = 'N/A';
            $partyType = 'N/A';
            if ($module === 'purchase') {
                $partyName = $record->supplier->company_name ?? ($before['supplier_name'] ?? 'N/A');
                $partyType = 'Supplier';
            } elseif ($module === 'sale') {
                $partyName = $record->customer->company_name ?? ($before['customer_name'] ?? 'N/A');
                $partyType = 'Customer';
            } elseif ($module === 'returnable') {
                $partyName = $record->customer->company_name ?? ($before['customer_name'] ?? 'N/A');
                $partyType = 'Customer';
            }

            // Products summary
            $itemsCount = 0;
            $productsSummary = 'No items';
            if (isset($record->items) && count($record->items) > 0) {
                $itemsCount = count($record->items);
                $names = [];
                foreach ($record->items as $it) {
                    $pName = $it->product->name ?? 'Product';
                    $qty = $it->quantity ?? 1;
                    $names[] = "{$pName} (x{$qty})";
                }
                $productsSummary = implode(', ', array_slice($names, 0, 3));
                if (count($names) > 3) {
                    $productsSummary .= ' +' . (count($names) - 3) . ' more';
                }
            } elseif (isset($before['items_count'])) {
                $itemsCount = $after['items_count'] ?? $before['items_count'];
                $productsSummary = $after['items_summary'] ?? $before['items_summary'] ?? 'N/A';
            }

            $grandTotal = (float)($record->grand_total ?? 0);
            $paidAmount = (float)($record->paid_amount ?? 0);
            $paymentStatus = $record->payment_status ?? ($module === 'returnable' ? 'returned' : 'pending');

            $moduleLabels = [
                'purchase'   => 'Purchase Order',
                'sale'       => 'Sales Challan',
                'returnable' => 'Returnable Entry',
            ];
            $moduleLabel = $moduleLabels[$module] ?? ucfirst($module);
            $userName = $user?->name ?? 'System';

            $changes = null;
            $description = $customDesc;

            if ($action === 'create') {
                $description = $customDesc ?? "New {$moduleLabel} #{$referenceNo} entered by {$userName} ({$partyType}: {$partyName}, Amount: ₹" . number_format($grandTotal, 2) . ").";
                $changes = [
                    'attributes' => [
                        'module'         => $moduleLabel,
                        'reference_no'   => $referenceNo,
                        'project_name'   => $projectName,
                        'party'          => "{$partyType}: {$partyName}",
                        'items_count'    => $itemsCount,
                        'items_summary'  => $productsSummary,
                        'grand_total'    => $grandTotal,
                        'paid_amount'    => $paidAmount,
                        'payment_status' => ucfirst($paymentStatus),
                    ]
                ];
            } elseif ($action === 'update') {
                $diffBefore = [];
                $diffAfter = [];
                $changeSummaries = [];

                if (is_array($before) && is_array($after)) {
                    $fieldLabels = [
                        'invoice_number'  => 'Invoice / Challan No',
                        'return_number'   => 'Return No',
                        'project_name'    => 'Project Name',
                        'supplier_name'   => 'Supplier',
                        'customer_name'   => 'Customer',
                        'grand_total'     => 'Grand Total',
                        'paid_amount'     => 'Paid Amount',
                        'payment_status'  => 'Payment Status',
                        'items_count'     => 'Total Items Count',
                        'items_summary'   => 'Products',
                    ];

                    foreach ($before as $key => $oldVal) {
                        $newVal = $after[$key] ?? null;
                        $isDifferent = false;
                        if (is_numeric($oldVal) && is_numeric($newVal)) {
                            $isDifferent = (float)$oldVal != (float)$newVal;
                        } else {
                            $isDifferent = (string)$oldVal !== (string)$newVal;
                        }

                        if ($isDifferent) {
                            $label = $fieldLabels[$key] ?? ucfirst(str_replace('_', ' ', $key));
                            $displayOld = in_array($key, ['grand_total', 'paid_amount']) ? '₹' . number_format((float)$oldVal, 2) : $oldVal;
                            $displayNew = in_array($key, ['grand_total', 'paid_amount']) ? '₹' . number_format((float)$newVal, 2) : $newVal;

                            $diffBefore[$key] = ['label' => $label, 'value' => $displayOld];
                            $diffAfter[$key] = ['label' => $label, 'value' => $displayNew];
                            $changeSummaries[] = "{$label}: {$displayOld} → {$displayNew}";
                        }
                    }
                }

                if (empty($changeSummaries)) {
                    $description = "{$moduleLabel} #{$referenceNo} updated by {$userName}.";
                } else {
                    $description = $customDesc ?? ("Updated {$moduleLabel} #{$referenceNo}: " . implode(', ', $changeSummaries));
                }

                $changes = [
                    'before' => $diffBefore,
                    'after'  => $diffAfter,
                ];
            } elseif ($action === 'delete') {
                $description = $customDesc ?? "Deleted {$moduleLabel} #{$referenceNo} by {$userName} ({$partyType}: {$partyName}, Amount: ₹" . number_format($grandTotal, 2) . ").";
                $changes = [
                    'attributes' => [
                        'module'         => $moduleLabel,
                        'reference_no'   => $referenceNo,
                        'project_name'   => $projectName,
                        'party'          => "{$partyType}: {$partyName}",
                        'items_count'    => $itemsCount,
                        'items_summary'  => $productsSummary,
                        'grand_total'    => $grandTotal,
                        'paid_amount'    => $paidAmount,
                        'payment_status' => ucfirst($paymentStatus),
                    ]
                ];
            }

            return self::create([
                'firm_id'          => $firmId,
                'user_id'          => $userId,
                'module'           => $module,
                'action'           => $action,
                'record_id'        => $action === 'delete' ? null : ($record->id ?? null),
                'reference_no'     => $referenceNo,
                'project_name'     => $projectName,
                'party_name'       => $partyName,
                'party_type'       => $partyType,
                'items_count'      => $itemsCount,
                'products_summary' => $productsSummary,
                'grand_total'      => $grandTotal,
                'paid_amount'      => $paidAmount,
                'payment_status'   => $paymentStatus,
                'description'      => $description,
                'changes'          => $changes,
                'ip_address'       => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to record TransactionAuditLog: ' . $e->getMessage());
            return null;
        }
    }
}

