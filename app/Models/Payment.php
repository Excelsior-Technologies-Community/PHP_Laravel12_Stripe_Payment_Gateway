<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'amount',
        'currency',
        'stripe_charge_id',
        'payment_intent_id',
        'status',
        'refund_id',
        'refunded_amount',
        'refund_status',
        'refund_reason',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
    ];

    public static function checkAndAddColumns()
    {
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'payment_intent_id')) {
                    $table->string('payment_intent_id')->nullable()->after('stripe_charge_id');
                }
                if (!Schema::hasColumn('payments', 'refund_id')) {
                    $table->string('refund_id')->nullable()->after('status');
                }
                if (!Schema::hasColumn('payments', 'refunded_amount')) {
                    $table->decimal('refunded_amount', 10, 2)->default(0)->after('refund_id');
                }
                if (!Schema::hasColumn('payments', 'refund_status')) {
                    $table->string('refund_status')->nullable()->after('refunded_amount');
                }
                if (!Schema::hasColumn('payments', 'refund_reason')) {
                    $table->text('refund_reason')->nullable()->after('refund_status');
                }
            });
        }
    }
}