<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(ContactRequest $request)
    {
        $data = $request->validated();

        Mail::to(env('MAIL_CONTACT_ADDRESS'))
            ->send(new ContactMail(
                name: $data['name'],
                email: $data['email'],
                phone: $data['phone'],
                service: $data['service'],
                userMessage: $data['message']
            ));

        return back()->with('success', 'Mensaje enviado correctamente');
    }
}