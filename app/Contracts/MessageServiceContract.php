<?php
namespace App\Contracts;

use App\Models\Message;

interface MessageServiceContract
{
    public function __construct(Message $message);

    public function send(): Message;
}