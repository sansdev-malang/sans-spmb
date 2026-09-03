<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payment_info' => 'array',
    ];

    public function scopeScopedByAdmin($query)
    {
        if (auth()->check() && auth()->user()->spmb_unit_id) {
            return $query->whereHas('registration', function ($q) {
                $q->where('spmb_unit_id', auth()->user()->spmb_unit_id);
            });
        }
        return $query;
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function items()
    {
        return $this->hasMany(PaymentItem::class, 'payment_id');
    }

    protected $appends = [
        'category_names',
        'channel_display_name',
    ];

    /**
     * Get dynamic category names from master SpmbFeeCategory for this payment
     */
    public function getCategoryNamesAttribute()
    {
        $info = is_array($this->payment_info) ? $this->payment_info : (json_decode($this->payment_info, true) ?: []);
        $categoryNames = [];

        if ($this->payment_type === 'registration_fee') {
            $fee = $this->registration ? $this->registration->getRegistrationFee() : null;
            if ($fee && $fee->category) {
                $categoryNames[] = $fee->category->name;
            } else {
                $cat = SpmbFeeCategory::where(function($q) {
                    $q->where('name', 'like', '%Formulir%')
                      ->orWhere('name', 'like', '%Pendaftaran%')
                      ->orWhere('name', 'like', '%Registrasi%');
                })->first();
                $categoryNames[] = $cat ? $cat->name : 'Formulir Pendaftaran';
            }
        } else {
            // Check relational payment_items first
            $relItems = $this->relationLoaded('items') ? $this->items : $this->items()->with('fee.category')->get();
            if ($relItems->isNotEmpty()) {
                foreach ($relItems as $pItem) {
                    if ($pItem->fee && $pItem->fee->category) {
                        $categoryNames[] = $pItem->fee->category->name;
                    } else {
                        $cat = SpmbFeeCategory::where('name', 'like', "%{$pItem->fee_name}%")->first();
                        $categoryNames[] = $cat ? $cat->name : $pItem->fee_name;
                    }
                }
            } else {
                // Fallback to legacy payment_info['selected_items']
                $selectedItems = $info['selected_items'] ?? [];
                if (!empty($selectedItems) && is_array($selectedItems)) {
                    foreach ($selectedItems as $it) {
                        $itName = $it['name'] ?? '';
                        if (!$itName) continue;

                        // 1. Look up in SpmbFee
                        $fee = SpmbFee::where('name', $itName)
                            ->when($this->registration, function($q) {
                                $q->where('spmb_unit_id', $this->registration->spmb_unit_id);
                            })->first();
                        if (!$fee) {
                            $fee = SpmbFee::where('name', $itName)->first();
                        }
                        if ($fee && $fee->category) {
                            $categoryNames[] = $fee->category->name;
                        } else {
                            // 2. Check if category matches extra service name directly
                            $cat = SpmbFeeCategory::where('name', 'like', "%{$itName}%")->first();
                            if ($cat) {
                                $categoryNames[] = $cat->name;
                            }
                        }
                    }
                }
            }
        }

        $categoryNames = array_values(array_unique(array_filter($categoryNames)));
        if (empty($categoryNames)) {
            $defaultCat = SpmbFeeCategory::where('name', '!=', 'Formulir Pendaftaran')->first();
            $categoryNames = [$defaultCat ? $defaultCat->name : 'Biaya Administrasi'];
        }

        return $categoryNames;
    }

    /**
     * Get user-friendly payment channel name (e.g. 'VA MANDIRI', 'QRIS', 'SHOPEEPAY')
     */
    public function getChannelDisplayNameAttribute()
    {
        $info = is_array($this->payment_info) ? $this->payment_info : [];

        // 1. Check direct bank name in payment_info (Virtual Accounts)
        if (!empty($info['bankName'])) {
            $b = strtoupper($info['bankName']);
            if (in_array($b, ['MANDIRI', 'BRI', 'BNI', 'BCA', 'BSI', 'PERMATA', 'CIMB'])) {
                return 'VA ' . $b;
            }
            return $b;
        }

        // 2. Check channel in additionalInfo
        if (!empty($info['additionalInfo']['channel'])) {
            return strtoupper($info['additionalInfo']['channel']);
        }

        // 3. Check QRIS
        if (!empty($info['qrisUrl']) || !empty($info['qrContent']) || (strtolower($this->payment_gateway_code ?? '') === 'qris')) {
            return 'QRIS';
        }

        // 4. Check e-wallet
        if (!empty($info['ewalletChannel'])) {
            return strtoupper($info['ewalletChannel']);
        }

        // 5. Fallback to gateway code (e.g. WINPAY, BNI)
        return strtoupper($this->payment_gateway_code ?? 'Winpay');
    }
}
