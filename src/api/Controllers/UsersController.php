<?php 
    namespace Controllers;
    use PHPMailer\PHPMailer\PHPMailer;

    class UsersController extends BaseController
    {
        private string $name, $email, $password;
        private int $id;

        public function __construct(...$args){
            parent::__construct(...$args);
            $this -> password = array_key_exists("password", $this -> post_data) ? $this -> post_data["password"] : "";;
            $this -> name = array_key_exists("name", $this -> post_data) ? $this -> post_data["name"] : "";
            $this -> email = array_key_exists("email", $this -> post_data) ? $this -> post_data["email"] : "";
            $this -> id = array_key_exists("id", $this -> post_data) ? intval($this -> post_data["id"]) : 0;
        }

        private function login($private_key, $public_key){
            $this -> name !== "" ? 
            $this -> resource -> validate($this -> password, $private_key, $public_key, $this -> name) :
            $this -> resource -> validate($this -> password, $private_key, $public_key, $this -> email);
        }

        private function register($private_key, $public_key){
            $this -> resource -> init();
            $this -> resource -> subscribe($this -> name, $this -> email, $this -> password, $private_key, $public_key);
        }

        private function save_new_password() {
            $this -> resource -> savePassword($this -> password, $this -> id);
        }

        private function send_retrieve_password_email(){
            $user = $this -> resource -> retrieveUserFromEmail($this -> email);

            ob_start();
            $user_name = $user["name"];
            $user_id = $user["id"];
            $protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https://" : "http://";
            $url = $protocol . $_SERVER["HTTP_HOST"] . "/views/recover_password.php?user_id={$user_id}";

            include ROOT_PATH . "../views/forgot_password.php";
            $body = ob_get_contents();
            ob_end_clean();

            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail -> isSMTP();
                $mail -> Host = "smtp.gmail.com";
                $mail -> SMTPAuth = true;
                $mail -> Username = "noreply.bookshelf.app@gmail.com";
                $mail -> Password = "bykerkvmpucqcurr";
                $mail -> SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail -> Port = 587;

                // Recipients
                $mail -> setFrom($mail -> Username);
                $mail -> addAddress($this -> email, $this -> name);

                // Content
                $mail -> CharSet = "UTF-8";
                $mail -> isHTML(true);
                $mail -> Subject = "Recuperação de senha";
                $mail -> Body = $body;

                $mail -> send();
                echo json_encode(["message" => "Message sent successfully to {$this -> email}"]);
            } catch (Exception $e) {
                echo json_encode(["message" => "Message could not be sent", "error" => $mail -> ErrorInfo]);
            }
        }

        public function set_response(){
            $request_type = $this -> params[0];
            $private_key = openssl_pkey_new([
                    "digest_alg" => "sha256", 
                    "private_key_bits" => 4096,
                    "private_key_type" => OPENSSL_KEYTYPE_RSA
                ]);

            $public_key = openssl_pkey_get_details($private_key)["key"];

            $name_is_set = $this -> name !== "";
            $email_is_set = $this -> email !== "";
            $password_is_set = $this -> password !== "";
            $id_is_set = $this -> id !== "";

            switch ($request_type) {
                case "login":
                    if ($password_is_set && ($name_is_set || $email_is_set)) {
                        $this -> login($private_key, $public_key);
                    } else {
                        http_response_code(400);
                        echo json_encode(["message" => "You must sent a password and a name or an email data to fullfil this request."]);
                    }
                    break;
                case "register":
                    if ($name_is_set && $email_is_set && $password_is_set) {
                        $this -> register($private_key, $public_key);
                    } else {
                        http_response_code(400);
                        echo json_encode(["message" => "You must sent a name, e-mail and password data to fullfil this request."]);
                    }
                    break;
                case "forgot_password":
                    if ($email_is_set) {
                        $this -> send_retrieve_password_email();
                    }
                    break;
                case "save_new_password":
                    if ($id_is_set && $password_is_set) {
                        $this -> save_new_password();
                    }
                    break;
            }
        }
    }
?>