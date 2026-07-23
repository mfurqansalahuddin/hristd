<?php

namespace App\Mail;

use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionProcessed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public AccountDeletionRequest $deletionRequest)
    {
        //
    }

    public function envelope(): Envelope
    {
        $subject = $this->deletionRequest->status === AccountDeletionRequest::STATUS_APPROVED
            ? 'Permintaan Penghapusan Akun Anda Telah Diproses'
            : 'Permintaan Penghapusan Akun Anda Ditolak';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-deletion-processed',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
