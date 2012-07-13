<?php

class Application_Form_NewNewsletter extends Zend_Form
{

    public function init()
    {
        //title 
        $title = $this->createElement('text', 'title');
        $title->setLabel('Title');
        $this->addElement($title);
        
        //body
        $body = $this->createElement('textarea', 'body');
        $body->setLabel('Text');
        $this->addElement($body);
        
        //template
        $templateModel = new Application_Model_DbTable_Template();
        $templates = $templateModel->fetchAll();
        
        $select = new Zend_Form_Element_Select('id_layout');
        $select->setLabel('Choose template');
        foreach ($templates as $template)
        {
            
            $select->addMultiOption($template->id , $template->name);
        }
        
        $this->addElement($select);
        
        //rules
        $rules = new Zend_Form_Element_MultiCheckbox('rules');
        $newsRulesModel = new Application_Model_DbTable_NewsRules();
        $newsRules = $newsRulesModel->fetchAll();
        
        foreach ($newsRules as $newsRule)
        {
            $rules->addMultiOption($newsRule->id , $newsRule->name);
        }
        
        $this->addElement($rules);
        
        //send option
        $sendOption = new Zend_Form_Element_Checkbox('senoption');
        $this->addElement($sendOption);
        
        //submit
        $submit = $this->createElement('submit', 'button');
        $submit->setLabel('Register newsletter');
        $this->addElement($submit);
    }


}

