<?php
/**
 * Class ContactForm
 *
 * Contact Form for the Dealer Inspire Code Challenge. Accepts required fields Full Name, Email and Message, and
 * option field Phone as POST input. Once validated, the form submission is sent to a specified contact and saved
 * in a MySQL database.
 *
 * To Do: Add configurations for outbound email options, like using an authenticated SMTP server.
 */

require __DIR__ . "/../vendor/phpmailer/phpmailer/src/PHPMailer.php";
require __DIR__ . "/../vendor/phpmailer/phpmailer/src/SMTP.php";
require __DIR__ . "/../vendor/phpmailer/phpmailer/src/Exception.php";

class ContactForm
{
    public $isValid = false;
    public $formErrors = array();
    public $messageSent = false;
    public $messageSaved = false;
    public $messageRecipent = "mez@aitg.com";
    public $messageSubject = "Contact Form Submission";
    protected $data = array();
    private $dbConn = null;
    private $dbconfig = array(
                            'host'      => '',
                            'user'      => '',
                            'password'  => '',
                            'dbname'    => ''
                        );

    /**
     * ContactForm constructor.
     * @param array $params - usually $_POST input from form
     * @throws Exception
     */
    public function __construct(array $params = array())
    {
            if (empty($params) || !is_array($params)) {
                throw new Exception("Invalid POST input");
            }
            $this->data = $params;
    }

    /**
     * @desc Sets the recipient of the email with the form submission
     * @param $recipient - to whom the email should be sent
     * @return bool - success or failure
     */
    public function setRecipient($recipient) {
        $recipient_sanitized = filter_var($recipient, FILTER_SANITIZE_EMAIL);
        if ($recipient_sanitized !== false) {
            if(filter_var($recipient_sanitized, FILTER_VALIDATE_EMAIL) === false){
                return false;
            } else {
                $this->messageRecipent = $recipient_sanitized;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * @desc Sets the subject line of the email
     * @param $subject - Subject line of outgoing email
     * @return bool - success or failure
     */
    public function setSubject($subject) {
        $subject_sanitized = filter_var($subject, FILTER_SANITIZE_STRING);
        $this->messageSubject = $subject_sanitized;
        return true;
    }

    /**
     * @desc Validates (and sanitizes) all input
     * Checks that required files (fullname, email, message) are set.
     * Validates email is valid.
     * Validates phone number against (arbitrary) rules for this form - see below.
     * @return bool - true (all input is valid), false (contains invalid input)
     */
    public function validateInput() {
        // Full Name - Required
        if (empty($this->data['fullname'])) {
            $this->formErrors[] = array('id'=>'cf-fullname', 'message'=>'Please enter your Full Name');
        } else {
            $fullname = filter_var($this->data['fullname'], FILTER_SANITIZE_STRING);
            $this->data['fullname'] = $fullname;
        }

        // Email - Required
        if (empty($this->data['email'])) {
            $this->formErrors[] = array('id'=>'cf-email', 'message'=>'Please enter your Email');
        } else {
            $email = filter_var($this->data['email'], FILTER_SANITIZE_EMAIL);
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $this->formErrors[] = array('id'=>'cf-email', 'message'=>'Please enter a valid Email');
            } else {
                $this->data['email'] = $email;
            }
        }

        // Phone - Optional
        // For purposes of this form, assuming valid phone numbers can contain only numbers, spaces, dashes and parens.
        // Fully validating any phone (international, extensions etc.) is much more difficult.
        if (!empty($this->data['phone'])) {
            $phone = filter_var($this->data['phone'], FILTER_SANITIZE_STRING);
            if (!preg_match("/[^0-9-() ]/", $phone)) {
                $this->data['phone'] = $phone;
            } else {
                $this->formErrors[] = array('id'=>'cf-phone', 'message'=>'Please enter a valid Phone');
            }
        }

        // Message - Required
        if (empty($this->data['message'])) {
            $this->formErrors[] = array('id'=>'cf-message', 'message'=>'Please enter a Message');
        } else {
            $message = filter_var($this->data['message'], FILTER_SANITIZE_STRING);
            $this->data['message'] = $message;
        }

        if(empty($this->formErrors)){
            $this->isValid = true;
        }

        return $this->isValid;
    }

    /**
     * @desc Sends the form submission via email to the recipient using PHPMailer.
     * @return bool - success or failure
     */
    public function sendMessage() {

        if ($this->isValid === false) {
            return false;
        }

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->From = $this->data['email'];
        $mail->FromName = $this->data['fullname'];
        $mail->addAddress($this->messageRecipent);
        $mail->addReplyTo($this->data['email'], "Reply");
        $mail->isHTML(true);
        $mail->Subject = $this->messageSubject;
        $mail->Body = $this->data['message'];

        if (!$mail->send()) {
            $this->messageSent = false;
        } else {
            $this->messageSent = true;
        }

        return $this->messageSent;
    }

    /**
     * @desc Sets up the configuration details for the database connection.
     * @param $host - hostname or ip of database server
     * @param $user - database user name
     * @param $password - database user password
     * @param $dbname - name of database
     * @return bool - success or failure
     * @throws Exception
     */
    public function setDB($host, $user, $password, $dbname='DI_ContactForm') {
        if(empty($host) || empty($user) || empty($password) || empty($dbname)) {
            throw new Exception("Could not initialize DB");
        }
        $this->dbconfig['host'] = $host;
        $this->dbconfig['user'] = $user;
        $this->dbconfig['password'] = $password;
        $this->dbconfig['dbname'] = $dbname;
        return true;
    }

    /**
     * @desc Connects to the database
     * @param array $dbconfig - database connection info set via setDB()
     * @return bool - success or failure
     * @throws Exception
     */
    public function dbConnect(array $dbconfig = array()){
        if(empty($this->dbconfig['host']) || empty($this->dbconfig['user']) || empty($this->dbconfig['password']) || empty($this->dbconfig['dbname'])) {
            throw new Exception("Please initialize db config");
        }

        $this->dbConn = new mysqli($this->dbconfig['host'], $this->dbconfig['user'], $this->dbconfig['password'], $this->dbconfig['dbname']);

        if($this->dbConn->connect_error){
            throw new Exception("Database connection failed");
        }
        return true;
    }

    /**
     * @desc Disconnects from the database
     * @return bool - success or failure
     */
    public function dbDisconnect(){
        if(!empty($this->dbConn) && is_object($this->dbConn)){
            $this->dbConn->close();
        }
        return true;
    }

    /**
     * @desc Saves the form submission into the database.
     * @return bool - success or failure
     * @throws Exception
     */
    public function saveMessage()
    {
        if ($this->dbConnect($this->dbconfig) === false) {
            throw new Exception("Database connection failed");
        }

        $stmt = $this->dbConn->prepare("INSERT INTO form_submissions (fullname, email, phone, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $this->data['fullname'], $this->data['email'], $this->data['phone'], $this->data['message']);
        $result = $stmt->execute();
        $stmt->close();
        $this->dbDisconnect();

        if ($result === true) {
            $this->messageSaved = true;
        } else {
            $this->formErrors[] = array('id'=>'', 'message'=>'There was a problem saving the message. Please try again.');
            $this->messageSaved = false;
        };

        return $this->messageSaved;
    }

}