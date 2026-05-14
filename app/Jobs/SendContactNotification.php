<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendContactNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly array $contact,
        public readonly string $adminEmail,
    ) {}

    public function handle(): void
    {
        Mail::raw(
            "New contact message from {$this->contact['name']} <{$this->contact['email']}>\n\nSubject: ".($this->contact['subject'] ?: 'No Subject')."\n\n{$this->contact['message']}",
            function ($m) {
                $m->to($this->adminEmail)
                    ->subject('New Contact Message: '.($this->contact['subject'] ?: 'No Subject'))
                    ->replyTo($this->contact['email'], $this->contact['name']);
            }
        );
    }
}
