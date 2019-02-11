<?php
namespace Basic;

use Medoo\Medoo;

class Auth
{
    private $db;
    private $domain;
    public function __construct($db=null, $domain=null)
    {
        if (is_null($db)) {
            die('auth error: medoo dependency injection fail');
        } else {
            $this->db = $db;
        }
        if (!is_null($domain)) {
            $this->domain=$domain;
        }
    }
    public function isAuth()
    {
        if (!isset($_COOKIE['id'])) {
            return false;
        }
        if (!isset($_COOKIE['token'])) {
            return false;
        }
        $where['AND']=[
            'id'=>@$_COOKIE['id'],
            'token'=>@$_COOKIE['token']
        ];
        $user=$this->db->get("users", '*', $where);
        if (isset($user['token_expiration'])) {
            if ($user['token_expiration']>time()) {
                return $user;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function logout()
    {
        $user=$this->isAuth();
        if (php_sapi_name() <> "cli") {
            setcookie("token", "", time()-3600, '/');
            setcookie("id", "", time()-3600, '/');
        }
        if ($user) {
            $data=[
                'token_expiration'=>time()-3600
            ];
            $this->db->update("users", $data, ['id'=>$user['id']]);
        }
        return true;
    }
    public function signin()
    {
        $this->logout();
        $email=@$_POST['email'];
        $password=@$_POST['password'];
        $error=false;
        $where=[
            'email'=>$email
        ];
        $user=$this->db->get("users", '*', $where);
        if (!$user) {
            $error[]='invalid_email';
        }
        if (password_verify($password, $user['password'])) {
            $id=$user['id'];
            $min=60;
            $hora=60*$min;
            $dia=24*$hora;
            $ano=365*$dia;
            $limit=time()+(2*$ano);
            $token=bin2hex(openssl_random_pseudo_bytes(32));
            $data=[
                'token'=>$token,
                'token_expiration'=>$limit
            ];
            $this->db->update("users", $data, ['id'=>$id]);
            if (isset($this->domain)) {
                setcookie("id", $id, $limit, '/', $this->domain);
                setcookie("token", $token, $limit, '/', $this->domain);
            } else {
                setcookie("id", $id, $limit, '/');
                setcookie("token", $token, $limit, '/');
            }
            return $this->db->get("users", "*", ['id'=>$id]);
        } else {
            $error[]='invalid_password';
        }
        if ($error) {
            return ['error'=>$error];
        }
    }
    public function signup($user=false)
    {
        $this->logout();
        $user['created_at']=time();
        if ($user===false) {
            $user=[
                'name'=>@$_POST['name'],
                'email'=>@$_POST['email'],
                'password'=>@$_POST['password'],
                'type'=>@trim(strtolower($_POST['type']))
            ];
        }
        $user['name']=trim($user['name']);
        $user['name']=strtolower($user['name']);
        $user['name']=ucwords($user['name']);
        $user['name']=preg_replace('/\s+/', ' ', $user['name']);
        $user['uuid']=$this->uuid();
        $error=false;
        if (preg_match('/^[a-z0-9 .\-]+$/i', $user['name']) && strlen($user['name'])>=3) {
            if (filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                if (strlen($user['password'])>=8) {
                    $user['password']=password_hash($user['password'], PASSWORD_DEFAULT);
                    if ($this->db->get("users", '*', ['email'=>$user['email']])) {
                        $error[]='invalid_email';
                    } else {
                        if($user['type']<>'admin' && $user['type']<>'user'){
                            $user['type']='user';
                        }
                        $data=[
                            'email'=>$user['email'],
                            'name'=>$user['name'],
                            'password'=>$user['password'],
                            'uuid'=>$user['uuid'],
                            'type'=>$user['type'],
                            'created_at'=>time()
                        ];
                        $this->db->insert("users", $data);
                        $id=$this->db->id();
                        if (is_numeric($id) && $id<>0) {
                            if (isset($_POST['email']) && isset($_POST['password'])) {
                                $this->signin();
                            } else {
                                return $id;
                            }
                        } else {
                            return false;
                        }
                    }
                } else {
                    $error[]='invalid_password';
                }
            } else {
                $error[]='invalid_email';
            }
        } else {
            $error[]='invalid_name';
        }
        if ($error) {
            return ['error'=>$error];
        }
    }
    function uuid($limit=11){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-';
        $uuid = '';
        for ($i = 0; $i < $limit; $i++) {
            $uuid .= $characters[rand(0, mb_strlen($characters)-1)];
        }
        $where=[
            'uuid'=>$uuid
        ];
        if($this->db->get('users','*',$where)){
            return $this->uuid();
        }else{
            return $uuid;
        }
    }
}
