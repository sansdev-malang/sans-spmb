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

    /**
     * Get logo URL from idn-finlogos or SpmbPaymentChannel for this payment.
     */
    public function getLogoUrl(): ?string
    {
        // 1. Try matching via SpmbPaymentChannel if payment_method matches code or name
        if (!empty($this->payment_method)) {
            $channel = SpmbPaymentChannel::where('code', $this->payment_method)
                ->orWhere('name', $this->payment_method)
                ->first();
            if ($channel) {
                $logo = $channel->getLogoUrl();
                if ($logo) {
                    return $logo;
                }
            }
        }

        // 2. Fallback: match by channel_display_name, payment_method, or payment_info
        $info = $this->payment_info;
        if (is_string($info)) {
            $info = json_decode($info, true);
        }
        $bankName = $info['bankName'] ?? '';
        $ewallet = $info['ewalletChannel'] ?? '';
        $channelKey = strtolower(($this->channel_display_name ?? '') . ' ' . ($this->payment_method ?? '') . ' ' . $bankName . ' ' . $ewallet);

        $logoSlugs = [
            'qris' => 'qris',
            'bca' => 'bca',
            'shopee' => 'shopee-pay',
            'mandiri' => 'mandiri',
            'dana' => 'dana',
            'bsi' => 'bsi',
            'bni' => 'bni',
            'bri' => 'bri',
            'indomaret' => 'indomaret',
            'alfamart' => 'alfamart',
            'permata' => 'permata',
            'cimb' => 'cimb-niaga',
            'gopay' => 'gopay',
            'ovo' => 'ovo',
            'linkaja' => 'linkaja',
        ];

        foreach ($logoSlugs as $keyword => $slug) {
            if (str_contains($channelKey, $keyword)) {
                $path = 'vendor/idn-finlogos/' . $slug . '.svg';
                if (file_exists(public_path($path))) {
                    $svg = file_get_contents(public_path($path));
                    if ($svg !== false) {
                        if (!str_contains($svg, 'xmlns=')) {
                            $svg = preg_replace('/<svg\b(?![^>]*\bxmlns=)/i', '<svg xmlns="http://www.w3.org/2000/svg"', $svg, 1);
                        }
                        return 'data:image/svg+xml;base64,' . base64_encode($svg);
                    }
                }
            }
        }

        return null;
    }
}

