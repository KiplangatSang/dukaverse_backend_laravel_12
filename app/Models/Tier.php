<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tier extends Model {
    use HasFactory, SoftDeletes;

    const RETAIL_TIERS   = "retails";
    const SUPPLIER_TIERS = "suppliers";

    const tier_types = [

        self::RETAIL_TIERS,
        self::SUPPLIER_TIERS,

    ];

    const HOURLYBILLINGDURATION   = 'Every 1 hr';
    const SIXHOURBILLINGDURATION  = 'Every 6 hrs';
    const DAILYBILLINGDURATION    = 'day';
    const WEEKLYBILLINGDURATION   = 'week';
    const MONTHLYBILLINGDURATION  = "month";
    const SIXMONTHBILLINGDURATION = "6 months";
    const YEARLYBILLINGDUARTION   = "year";

    const BILLINGDURATIONS = [
        self::HOURLYBILLINGDURATION,
        self::SIXHOURBILLINGDURATION,
        self::DAILYBILLINGDURATION,
        self::WEEKLYBILLINGDURATION,
        self::MONTHLYBILLINGDURATION,
        self::SIXMONTHBILLINGDURATION,
        self::YEARLYBILLINGDUARTION,
    ];

    protected $guarded = [];

    protected $casts = [
        'benefits' => JsonCast::class,
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function tierable()
    {
        return $this->morphTo();
    }

    public function tierItems()
    {
        return $this->hasMany(TierItem::class, 'tier_id');
    }

}
