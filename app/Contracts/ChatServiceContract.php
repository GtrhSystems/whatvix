<?php
namespace App\Contracts;
use App\Models\Message;
use Illuminate\Http\Request;

interface ChatServiceContract
{
    public function __construct(Request $request);
    
    public function generateMessage(Request $data): Message;

    public function sendMessage(): Message;

}