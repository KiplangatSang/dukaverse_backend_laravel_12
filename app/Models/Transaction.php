<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model {
    use HasFactory, SoftDeletes;
    //
    protected $guarded         = [];
    const INTERNAL             = "internal";
    const EXTERNAL_TO_INTERNAL = "external_to_internal";
    const INTERNAL_TO_EXTERNAL = "internal_to_external";
    const EXTERNAL             = "external";

    const TRASACTION_TYPES = [
        self::INTERNAL,
        self::EXTERNAL_TO_INTERNAL,
        self::INTERNAL_TO_EXTERNAL,
        self::EXTERNAL,
    ];

    const AWAITING_AUTHORIZATION = 'awaiting authorization';
    const FAILED                 = 'failed';
    const UNPAID                 = 'not paid';
    const PAID                   = 'paid';
    const AWAITING_REFUND        = 'awaiting refund';
    const REFUNDED               = 'refunded';

    const TRANSACTION_STATUS = [
        self::AWAITING_AUTHORIZATION,
        self::FAILED,
        self::UNPAID,
        self::PAID,
        self::AWAITING_REFUND,
        self::REFUNDED,
    ];

    public function getTransactionType($payment_method): int
    {

        switch ($payment_method) {
            case $payment_method === "MPESA" || $payment_method === "Mpesa" || $payment_method === "mpesa":
                return self::EXTERNAL_TO_INTERNAL;
                break;
            case $payment_method === "CASH" || $payment_method === "Cash" || $payment_method === "cash":
                return self::EXTERNAL;
                break;
            default:
                return self::EXTERNAL;
                break;
        }

    }

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function transactionable()
    {
        # code...
        return $this->morphTo();
    }

    public function purposeable()
    {
        # code...
        return $this->morphTo();
    }

    public function sendable()
    {
        # code...
        return $this->morphTo();
    }

    public function receivable()
    {
        # code...
        return $this->morphTo();
    }

    public function senderAccount()
    {
        # code...
        return $this->belongsTo(Account::class, "sender_accounts_id");
    }

    public function receiverAccount()
    {
        # code...

        return $this->belongsTo(Account::class, "receiver_accounts_id");
    }

    public function receipt()
    {

        return $this->hasOne(Receipt::class, 'transaction_id');
    }
}
