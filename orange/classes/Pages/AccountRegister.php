<?php

namespace Orange\Pages;
use Orange\MiscFunctions;
use Orange\User;

/**
 * Backend code for the register page.
 *
 * @since 0.1.0
 */
class AccountRegister
{
    private \Orange\Database $database;
    private \Orange\Orange $orange;

    public function __construct(\Orange\Orange $betty)
    {
        $ipcheck = file_get_contents("https://api.stopforumspam.org/api?ip=" . miscFunctions::get_ip_address());

        if (str_contains($ipcheck, "<appears>yes</appears>")) {
            $betty->Notification("This IP address appears to be suspicious.", "/index.php");
        }

        $this->database = $betty->getBettyDatabase();
        $this->orange = $betty;
    }

    public function postData(array $POST)
    {
        global $gump;

        $error = '';

        $gump->validation_rules([
            "username" => "required|alpha_numeric|max_len,128|min_len,1",
            "pass1"    => "required|max_len,128|min_len,8",
            "pass2"    => "required|equalsfield,pass1",
            "email"    => "required|valid_email|max_len,128",
        ]);

        $gump->filter_rules([
            "username" => "trim",
            "pass1"    => "trim",
            "pass2"    => "trim",
            "email"    => "trim|sanitize_email",
        ]);

        $filter = $gump->run($POST);

        if ($gump->errors()) {
            $error = $gump->get_errors_array();
            $error_message = '';
            foreach ($error as $error_data) {
                $error_message = $error_message . $error_data . '. ';
            }
            $this->orange->Notification($error_message, "/register.php");
        } else {
            $username = (string)$filter['username'];
            $pass = $filter['pass1'];
            $pass2 = $filter['pass2'];
            $mail = (string)filter_var($filter['email'], FILTER_SANITIZE_EMAIL);

            if ($this->database->result("SELECT COUNT(*) FROM users WHERE name = ?", [$username])) $error .= "Username has already been taken. "; //ashley2012 bypassed this -gr 7/26/2021
            if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $username)) $error .= "Username contains invalid characters (Only alphanumeric and underscore allowed). "; //ashley2012 bypassed this with the long-ass arabic character. -gr 7/26/2021
            if ($this->database->result("SELECT COUNT(*) FROM users WHERE email = ?", [$mail])) $error .= "You've already registered an account using this email address. ";
            if ($this->database->result("SELECT COUNT(*) FROM users WHERE ip = ?", [miscFunctions::get_ip_address()]) > 10)
                $error .= "Creating more than 10 accounts isn't allowed. ";

            if(!$error) {
                $token = bin2hex(random_bytes(32));
                $this->database->query("INSERT INTO users (name, password, token, joined, lastview, title, email) VALUES (?,?,?,?,?,?,?)",
                    [$username, password_hash($pass, PASSWORD_DEFAULT), $token, time(), time(), $username, $mail]);

                setcookie('SBTOKEN', $token, 2147483647);

                MiscFunctions::redirect('./');
            } else {
                $this->orange->Notification($error, "/register.php");
            }
        }
    }
}