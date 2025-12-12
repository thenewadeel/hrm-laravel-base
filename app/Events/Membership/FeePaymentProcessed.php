<?php

namespace App\Events\Membership;

use App\Models\Membership\MemberFee;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeePaymentProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MemberFee $fee,
        public array $paymentData,
        public ?User $processedBy = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('organization.'.$this->fee->organization_id),
        ];
    }
}
