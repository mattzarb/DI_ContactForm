<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../classes/class.ContactForm.php';

class ContactFormTest extends TestCase
{
    public $post = array('fullname'=>'Bilbo Baggins','email'=>'bilbo@devnull.net','phone'=>'555-555-5555','message'=>'I like bacon');
    public $post_nophone = array('fullname'=>'Bilbo Baggins','email'=>'bilbo@devnull.net','phone'=>'','message'=>'I like bacon');
    public $post_noname = array('fullname'=>'','email'=>'bilbo@devnull.net','phone'=>'555-555-5555','message'=>'I like bacon');
    public $post_noemail = array('fullname'=>'Bilbo Baggins','email'=>'','phone'=>'555-555-5555','message'=>'I like bacon');
    public $post_nomessage = array('fullname'=>'Bilbo Baggins','email'=>'bilbo@devnull.net','phone'=>'555-555-5555','message'=>'');
    public $post_invalid_email = array('fullname'=>'Bilbo Baggins','email'=>'bilbo.devnull','phone'=>'555-555-5555','message'=>'I like bacon');
    public $post_invalid_phone = array('fullname'=>'Bilbo Baggins','email'=>'bilbo@devnull.net','phone'=>'555-555-5555x123','message'=>'I like bacon');
    public $post_phone_parens = array('fullname'=>'Bilbo Baggins','email'=>'bilbo@devnull.net','phone'=>'(555) 555-5555','message'=>'I like bacon');
    public $post_phone_spaces = array('fullname'=>'Bilbo Baggins','email'=>'bilbo@devnull.net','phone'=>'555 555 5555','message'=>'I like bacon');
    public $post_phone_digits = array('fullname'=>'Bilbo Baggins','email'=>'bilbo@devnull.net','phone'=>'5555555555','message'=>'I like bacon');

    public function testSetRecipient() {
        $recipient = 'bilbo@devnull.net';
        $form = new ContactForm($this->post);
        $form->setRecipient($recipient);

        $this->assertEquals($recipient, $form->messageRecipent);
    }

    public function testSetRecipient_BadEmail() {
        $recipient = 'bilbo.devnull';
        $form = new ContactForm($this->post);
        $retval = $form->setRecipient($recipient);

        $this->assertFalse($retval);
    }

    public function testSetSubject() {
        $subject = 'Form Message Subject';
        $form = new ContactForm($this->post);
        $form->setSubject($subject);

        $this->assertEquals($subject, $form->messageSubject);
    }

    public function testValidation_GoodInput() {
        $form = new ContactForm($this->post);
        $form->validateInput();

        $this->assertTrue($form->isValid);
    }

    public function testValidation_GoodInput_NoPhone() {
        $form = new ContactForm($this->post_nophone);
        $form->validateInput();

        $this->assertTrue($form->isValid);
    }

    public function testValidation_NoName() {
        $form = new ContactForm($this->post_noname);
        $form->validateInput();

        $this->assertFalse($form->isValid);
    }

    public function testValidation_NoEmail() {
        $form = new ContactForm($this->post_noemail);
        $form->validateInput();

        $this->assertFalse($form->isValid);
    }

    public function testValidation_NoMessage() {
        $form = new ContactForm($this->post_nomessage);
        $form->validateInput();

        $this->assertFalse($form->isValid);
    }

    public function testValidation_BadEmail() {
        $form = new ContactForm($this->post_invalid_email);
        $form->validateInput();

        $this->assertFalse($form->isValid);
    }

    public function testValidation_BadPhone() {
        $form = new ContactForm($this->post_invalid_phone);
        $form->validateInput();

        $this->assertFalse($form->isValid);
    }

    public function testValidation_PhoneWithParens() {
        $form = new ContactForm($this->post_phone_parens);
        $form->validateInput();

        $this->assertTrue($form->isValid);
    }

    public function testValidation_PhoneWithSpaces() {
        $form = new ContactForm($this->post_phone_spaces);
        $form->validateInput();

        $this->assertTrue($form->isValid);
    }

    public function testValidation_PhoneAllDigits() {
        $form = new ContactForm($this->post_phone_digits);
        $form->validateInput();

        $this->assertTrue($form->isValid);
    }

    public function testSendEmail() {
        $form = new ContactForm($this->post);
        $form->setRecipient('mez@aitg.com');
        $form->validateInput();
        $form->sendMessage();

        $this->assertTrue($form->messageSent);
    }

    public function testSaveMessage() {
        $form = new ContactForm($this->post);
        $form->validateInput();
        $form->setDB('172.31.0.214', 'diuser', 'd1Ch@allenge', 'DI_ContactForm');
        $form->saveMessage();

        $this->assertTrue($form->messageSaved);
    }

}