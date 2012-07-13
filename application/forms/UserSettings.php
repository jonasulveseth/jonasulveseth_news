<?php

class Application_Form_UserSettings extends Zend_Form
{

    public function init()
    {
        /**
         * Email 
         */
        $email = $this->createElement('text', 'epost');
        $email->setLabel('Your email');
        
        $userId = $this->getAttrib('userId');
        
        /**
         * rules 
         */
        $rulesModel = new Application_Model_DbTable_NewsRules();
        $rules = $rulesModel->fetchAll(array('public = ?' => '1'));

        
        $rulesElement = new Zend_Form_Element_MultiCheckbox('rules');
        $valuesArray = array();
        foreach ($rules as $rule)
        {
            
            //control if user has this option
            $userRightsModel = new Application_Model_DbTable_MemberHasNewsRule();
            $userRight = $userRightsModel->fetchRow(array('id_member = ?' => $userId , 'id_rule = ?' => $rule->id));
            
            
            if($userRight['access'] == '1')
            {
                 
               $rulesElement->addMultiOptions(array($rule->id => $rule->name) , $rule->id);
               $valuesArray[$rule->id] = $rule->id; 

            }
            if($userRight['access'] == '0')
            {
                
                $rulesElement->addMultiOptions(array($rule->id => $rule->name),$rule->id);     
                
            }
            if(null == $userRight['access'])
            {
                
               $rulesElement->addMultiOptions(array($rule->id => $rule->name) , $rule->id);
               $valuesArray[$rule->id] = $rule->id; 
            }
                        
        } 
        $rulesElement->setValue($valuesArray);
        $this->addElement($rulesElement);
        
        /**
         * button 
         */
        $button = $this->createElement('submit', 'button');
        $button->setLabel('Update your settings');
        $this->addElement($button);
    }


}

