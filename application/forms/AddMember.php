<?php

class Application_Form_AddMember extends Zend_Form
{

    public function init()
    {
        /**
         *Epost 
         */
        $epost = $this->createElement('text', 'epost');
        $epost->setLabel('Email');
        $this->addElement($epost);
        
        /**
         *Submit 
         */
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('Add this email');
        $this->addElement($submit);
    }


}

