<?php

class secret{
    
    public $hash;
    //string
    //Unique hash to identify the secrets
    
    public $secretText;
    //string
    //The secret itself
    
    public $createdAt;
    //string($date-time)
    //The date and time of the creation
    
    public $expiresAt;
    //string($date-time)
    //The secret cannot be reached after this time
    
    public $remainingViews;
    //integer($int32)
    //How many times the secret can be viewed

    protected $hashalgo = "md5";
    public function hash()
    {
        return hash($this->hashalgo, json_encode($this), false);
    }
       
}