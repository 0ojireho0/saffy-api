<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNotificationMail;
use App\Models\ContactForm;

class ContactUsController extends Controller
{
    //

    public function submitContactForm(Request $request){


        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
            'subject' => $request->subject
        ];

        Mail::to('jeremiahquintano20@gmail.com')->send(new SendNotificationMail($data));

        ContactForm::create($data);

        return 'email sent successfully';
    }
}
