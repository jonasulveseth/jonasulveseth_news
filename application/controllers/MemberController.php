<?php

class MemberController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        // action body
    }

    public function addSubscriptionAction() {

        //get email
        $emailParam = $this->_getParam('email');
        $memberModel = new Application_Model_DbTable_Member();


        if (null != $emailParam) {
            //try to fetch user to se if he exists from before

            $member = $memberModel->fetchRow(array('epost = ?' => $emailParam));
            if (null != $member) {
                $url = '<a href=' . $this->view->url(array('controller' => 'member', 'action' => 'manage-subscriptions', 'email' => $member->epost)) . '>Settings</a>';
                $this->view->message = sprintf('This Emailaddress is registered from before do you not receive email from us, please check yor settings on this page %s', $url);
            } else {
                //insert a new member
                $member = $memberModel->createRow();
                $member->epost = $emailParam;
                $member->save();
            }
        } else {
            $form = new Application_Form_AddMember();
            $this->view->form = $form;
        }

        $request = $this->getRequest();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            
            $member = $memberModel->fetchRow(array('epost = ?' => $form->getValue('epost')));
            
            if (null != $member) {
                $url = '<a href=' . $this->view->url(array('controller' => 'member', 'action' => 'manage-subscription', 'email' => $member->epost)) . '>Settings</a>';
                $this->view->message = sprintf('This Emailaddress is registered from before do you not receive email from us, please check yor settings on this page %s', $url);
            
                
            } else {
                $member = $memberModel->createRow();
                $member->setFromArray($form->getValues());
                $member->save();

                $this->view->message = 'Your email has been added';
            }
        }
    }

    public function manageSubscriptionAction() {
        $request = $this->getRequest();
        $emailParam = $this->_getParam('email');

        //get user based on email
        $userModel = new Application_Model_DbTable_Member();
        $user = $userModel->fetchRow(array('epost = ?' => $emailParam));

        $form = new Application_Form_UserSettings(array('userId' => $user->ID));
        
        //do a check and fill eampty fields with
        

        if ($request->isPost() && $form->isValid($request->getPost())) {
            
            $rulesModel = new Application_Model_DbTable_NewsRules();
            $rules = $rulesModel->fetchAll(array('public = ?' => '1'));
            
            
            // delete earlier rules
            $memberRulesModel = new Application_Model_DbTable_MemberHasNewsRule();
            $memberRulesModel->delete(array('id_member = ?' => $user->ID));

            foreach($rules as $rule)
            {
              $memberRules = $memberRulesModel->createRow();
              $memberRules->id_member = $user->ID;
              $memberRules->id_rule = $rule->id;
              $memberRules->access = '0';
              $memberRules->save();
            }

            //add new rows
            $ruleArray = $form->getValue('rules');
          
            foreach ($ruleArray as $rule => $value) {
                $memberRulesModel->update(array('access' => '1'), array('id_rule = ?' => $value));
            }

            echo "you have updated your settings";
        }

        $this->view->form = $form;
    }

}

