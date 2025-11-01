<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function subscriptionable()
    {
        return $this->morphTo();
    }

    public function terminals()
    {
        return $this->morphedByMany(Terminal::class, 'subscribable');
    }

    public function retails()
    {
        # code...
        return $this->belongsTo(Retail::class, 'retail_id');
    }

}
