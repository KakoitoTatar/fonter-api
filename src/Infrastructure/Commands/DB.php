<?php

declare (strict_types=1);

namespace App\Infrastructure\Commands;

use Exception;
use PDO;

class DB{
    protected $connection;
    protected $password;
    protected $dsn;
    protected $maxReconnectTries = 100;
    protected $reconnectErrors = [
        1317 // interrupted
        ,2002 // refused
        ,2006 // gone away
    ];
    protected $reconnectTries = 0;
    protected $reconnectDelay = 400; // in ms
    protected $user;
    protected $options = Array(
        PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION
    );
    public function __construct($dsn, $user = null, $password = null, $options = null){
        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
        if($options){
            $this->options = $options;
        }
    }
    public function getConnection(){
        if(!$this->connection){
            $this->connection = new PDO($this->dsn, $this->user, $this->password, $this->options);
        }
        return $this->connection;
    }
    public function query($query, $params = Array()){
        $conn = $this->getConnection();
        if(is_string($query) && $params){
            $query = $conn->prepare($query);
        }
        try{
            if(is_string($query)){
                return $conn->query($query);
            }else{
                $query->execute($params);
                return $query;
            }
        }catch(Exception $e){
            if(isset($e->errorInfo) && in_array($e->errorInfo[1], $this->reconnectErrors)){
                try{
                    $this->reconnect();
                }catch(Exception $e2){}
                return $this->query($query->queryString, $params);
            }
            throw $e;
        }
    }
    public function reconnect(){
        $connected = false;
        while(!$connected && $this->reconnectTries < $this->maxReconnectTries){
            usleep($this->reconnectDelay * 1000);
            ++$this->reconnectTries;
            $this->connection = null;
            try{
                if($this->getConnection()){
                    $connected = true;
                }
            }catch(Exception $e){}
        }
        if(!$connected){
            throw $e;
        }
    }
}

