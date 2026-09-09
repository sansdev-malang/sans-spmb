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
        $info = $this->payment_info;
        if (is_string($info)) {
            $info = json_decode($info, true);
        }
        if (!is_array($info)) {
            $info = [];
        }

        $categoryNames = [];

        if ($this->payment_type === 'registration_fee') {
            $fee = $this->registration ? $this->registration->getRegistrationFee() : null;
            if ($fee && $fee->category) {
                $categoryNames[] = $fee->category->name;
            } else {
                $cat = SpmbFeeCategory::where(function($q) {
                    $q->where('name', 'like', '%Formulir%')
                      ->orWhere('name', 'like', '%Pendaftaran%')
                      ->orWhere('name', 'like', '%Registrasi%')
                      ->orWhere('name', 'like', '%Enrollment%')
                      ->orWhere('name', 'like', '%Registration%');
                })->first();
                $categoryNames[] = $cat ? $cat->name : 'Biaya Pendaftaran';
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
            $defaultCat = SpmbFeeCategory::where(function($q) {
                $q->where('name', 'not like', '%Formulir%')
                  ->where('name', 'not like', '%Pendaftaran%')
                  ->where('name', 'not like', '%Registrasi%')
                  ->where('name', 'not like', '%Enrollment%')
                  ->where('name', 'not like', '%Registration%');
            })->first();
            $categoryNames = [$defaultCat ? $defaultCat->name : 'Biaya Administrasi'];
        }

        return $categoryNames;
    }

    /**
     * Get user-friendly payment channel name (e.g. 'VA MANDIRI', 'QRIS', 'SHOPEEPAY')
     */
    public function getChannelDisplayNameAttribute()
    {
        $info = $this->payment_info;
        if (is_string($info)) {
            $info = json_decode($info, true);
        }
        if (!is_array($info)) {
            $info = [];
        }

        // 1. Check direct bank name in payment_info (Virtual Accounts)
        if (!empty($info['bankName'])) {
            $b = strtoupper($info['bankName']);
            if (in_array($b, ['MANDIRI', 'BRI', 'BNI', 'BCA', 'BSI', 'PERMATA', 'CIMB'])) {
                return 'VA ' . $b;
            }
            return $b;
        }

        // 2. Check payment_method column directly
        if (!empty($this->payment_method)) {
            $m = strtoupper($this->payment_method);
            if (in_array($m, ['MANDIRI', 'BRI', 'BNI', 'BCA', 'BSI', 'PERMATA', 'CIMB'])) {
                return 'VA ' . $m;
            }
            if ($m === 'QRIS') {
                return 'QRIS';
            }
            return $m;
        }

        // 3. Check channel in additionalInfo
        if (!empty($info['additionalInfo']['channel'])) {
            return strtoupper($info['additionalInfo']['channel']);
        }

        // 4. Check QRIS
        if (!empty($info['qrisUrl']) || !empty($info['qrContent']) || (strtolower($this->payment_gateway_code ?? '') === 'qris')) {
            return 'QRIS';
        }

        // 5. Check e-wallet
        if (!empty($info['ewalletChannel'])) {
            return strtoupper($info['ewalletChannel']);
        }

        // 6. Fallback to gateway code (e.g. WINPAY, BNI)
        return strtoupper($this->payment_gateway_code ?? 'Winpay');
    }
}
