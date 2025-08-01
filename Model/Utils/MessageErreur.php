<?php

class MessageErreur
{
    public $title;
    public $message;

    public function __construct($newTitle = null, $newMessage = null)
    {
        $this->title = $newTitle ?? "Erreur";
        $this->message = $newMessage ?? "Une erreur est survenue. Veuillez réessayer plus tard.";
    }
}
