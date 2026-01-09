<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'subject' => 'required|min:5',
            'message' => 'required|min:10',
        ]);

        Mail::raw(
            "Nombre: {$data['name']}\nEmail: {$data['email']}\n\n{$data['message']}",
            function ($mail) use ($data) {
                $mail->to('developerbit035@gmail.com')
                     ->subject($data['subject']);
            }
        );

        return response()->json(['message' => 'OK']);
    }
}
