<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Alert extends Mailable
{
    use Queueable, SerializesModels;

    protected $rate;
    protected $increasesBtc;
    protected $decreasesBtc;
    protected $date;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($rate, $increasesBtc, $decreasesBtc, $date)
    {
        $this->rate = $rate;
        $this->increasesBtc = $increasesBtc;
        $this->decreasesBtc = $decreasesBtc;
        $this->date = $date;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('api.mail.alert.sender.adress'), config('api.mail.alert.sender.name'))
                    ->subject(config('api.mail.alert.subject'))
                    ->view('alertMail')
                    ->with([
                        'rate' => $this->rate,
                        'increasesBtc' => $this->increasesBtc,
                        'decreasesBtc' => $this->decreasesBtc,
                        'date' => $this->date,
                    ]);
    }
}
