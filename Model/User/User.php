<?php

class User
{
    private $id = 0;

    public function __construct($newId = 0)
    {
        $this->setId($newId);
    }

    public function getId()
    {
        return $this->id;
    }

    public function isIdValid()
    {
        return $this->id > 0;
    }

    protected function setId($newId)
    {
        $this->id = $newId;
    }
}
