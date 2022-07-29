<?php

class secretcontroller
{
    private $db;
    private $headers;
    private $statuscode=404;
    private $responsemessage="Secret not found";
    
    private static $instance = null;
    public static function getController()
    {
        try{
            $db = database::getConnection();
            self::$instance = new secretcontroller($db);
        }catch(Exception $ex)
        {
            echo "Controller cant load: ".$ex->getMessage();
        }

        return self::$instance;
    }

    private function __construct($db)
    {
        $this->db = $db;
        $this->headers = apache_request_headers();
        $this->acceptresponse = $this->headers["Accept"];
    }

    private function InsertNewSecret($request, &$secret)
    {
        $secret = new secret();

        $secret->secretText = $request["secret"];
        $secret->createdAt = date("Y-m-d H:i:s");
        $secret->expiresAt = date("Y-m-d H:i:s", strtotime('+'.$request["expireAfter"].' minutes',strtotime($secret->createdAt)));
        $secret->remainingViews = $request["expireAfterViews"];
        $secret->hash = $secret->hash();
        

        $query = "INSERT INTO `secret`
        (
        `hash`,
        `secretText`,
        `createdAt`,
        `expiresAt`,
        `remainingViews`)
        VALUES
        (
            '$secret->hash',
            '$secret->secretText',
            '$secret->createdAt',
            '$secret->expiresAt',
            '$secret->remainingViews');
        ";
        
        $this->db->query($query);
    }

    // decrease remainingviews number
    private function UpdateRemainingViews($result)
    {
        $query = "UPDATE secret 
        SET remainingViews = remainingViews -1
        WHERE hash='$result->hash'";

        $this->db->query($query);
    }

    // get secret modell by hash
    private function GetSecretByHash($hash)
    {
        $actualTime = date("Y-m-d H:i:s");
        $query = "SELECT 
            `hash`,
            `secretText`,
            `createdAt`,
            `expiresAt`,
            `remainingViews`
         FROM secret 
         WHERE hash='$hash' AND
            expiresAt>'$actualTime' AND
            remainingViews>'0'
         ";
       
        $result=null;
        if ($results = $this->db->query($query)) {
            while($obj = $results->fetch_object()){
                $result = $obj;
            }
            $results->close();
            unset($obj);
        }
        return $result;
    }

    // check request params contains or exists hash param
    private function CheckHashExistsInRequest($request)
    {
        if(empty($request) && !is_array($request) && count($request)>1){return false;}
        if(!isset($request['hash'])){return false;}
        return true;
    }

    //check request params contains or exists data to create new secret record 
    private function CheckNewSecretRequestParameters($request)
    {
        if(empty($request) && !is_array($request) && count($request)!=3){return false;}
        if(!isset($request["secret"])||!isset($request["expireAfterViews"])||!isset($request["expireAfter"])){return false;}
        return true;
    }

    // get hash value from request params
    private function GetHashFromRequest($request)
    {
        if(isset($request['hash'])){
            return $request['hash'];
        }
        return null;
    }

    // set headet status code
    private function setHeader($statuscode)
    {
        header("HTTP/1.0 $statuscode");
    }

    // get encoded response message
    private function getResponseMessage($result)
    {
        switch($this->acceptresponse)
        {
            case "application/xml" :
                {
                    return xmlrpc_encode($result);
                    break;
                }
            case "application/json" :
                {
                    return json_encode($result);
                    break;
                }
        }
    }

    // doing GET request
    public function doGetRequest($request)
    {
        if($this->CheckHashExistsInRequest($request))
        {
            $hash = $this->GetHashFromRequest($request);
            $result = $this->GetSecretByHash($hash);

            if($result!=null)
            {
                $this->UpdateRemainingViews($result);
                $this->statuscode = 200;
                $this->responsemessage = $this->getResponseMessage($result);
            }
            
        }
        
        $this->setHeader($this->statuscode);
        echo $this->responsemessage;

    }

    // doing POST request
    public function doPostRequest($request)
    {
        $this->statuscode = 405;
        $this->responsemessage = "Invalid input";

        if($this->CheckNewSecretRequestParameters($request))
        {
            $this->InsertNewSecret($request, $secret);

            $this->statuscode = 200;
            $this->responsemessage = $this->getResponseMessage($secret);
        }
         
        $this->setHeader($this->statuscode);
        echo $this->responsemessage;
    }
}