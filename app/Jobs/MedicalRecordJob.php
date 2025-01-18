<?php

namespace App\Jobs;

use App\Mail\MedicalRecordMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class MedicalRecordJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user, private string $accessCode)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email, $this->user->name)->send(new MedicalRecordMail(data: ["access_code" => $this->accessCode, "msg" => trans("mail.content_medical_record")]));
    }
}
