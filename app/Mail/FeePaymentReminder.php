<?php

namespace App\Mail;

use App\Models\Membership\MemberFee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeePaymentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public MemberFee $fee;

    public string $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(MemberFee $fee, string $customMessage = '')
    {
        $this->fee = $fee;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Reminder - '.$this->fee->description,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.fee-payment-reminder',
            with: [
                'fee' => $this->fee,
                'member' => $this->fee->member,
                'customMessage' => $this->customMessage,
                'isOverdue' => $this->fee->is_overdue,
                'daysOverdue' => $this->fee->days_overdue,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
