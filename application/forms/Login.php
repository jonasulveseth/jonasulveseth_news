<?php
/**
 * Lumiastock
 *
 * @category   Lumiastock
 * @package    Lumiastock
 * @copyright  Copyright (c) 2010 Lumia AB (http://www.lumia.se)
 * @version    $Id$
 */
class Application_Form_Login extends Zend_Form
{
  public function init()
  {
    $email = $this->createElement('text', 'email');
    $email->setLabel('Email');
    $email->setRequired();
    $this->addElement($email);

    $password = $this->createElement('password', 'password');
    $password->setRequired();
    $password->setLabel('Password');
    $this->addElement($password);

    $submit = $this->createElement('submit', 'Login');
    $submit->setLabel('Log in');
    $this->addElement($submit);

  }




}



