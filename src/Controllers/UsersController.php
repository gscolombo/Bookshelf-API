<?php 
    namespace Controllers;

    class UsersController extends BaseController
    {
        public function __construct(...$args){
            parent::__construct(...$args);
        }

        public function set_response(){            
            $request_type = $this -> params[0];
            $password = $this -> post_data['password'];
            $private_key = openssl_pkey_new([
                    "digest_alg" => "sha256", 
                    "private_key_bits" => 4096,
                    "private_key_type" => OPENSSL_KEYTYPE_RSA
                ]);

            $public_key = openssl_pkey_get_details($private_key)["key"];

            if (array_key_exists("email", $this -> post_data)) {
                $email = $this -> post_data['email'];
            }

            if (array_key_exists("name", $this -> post_data)) {
                $name = $this -> post_data['name'];
            }

            if (isset($name) && isset($email)) {
                $this -> resource -> subscribe($name, $email, $password, $private_key, $public_key);
            } else if ($request_type === "login") {
                isset($name) ? 
                $this -> resource -> validate($password, $private_key, $public_key, $name) :
                $this -> resource -> validate($password, $private_key, $public_key, $email);   
            }
        }
    }
?>