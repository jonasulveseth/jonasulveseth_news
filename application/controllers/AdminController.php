<?php

class AdminController extends Zend_Controller_Action {

    public function init() {
        if (Zend_Auth::getInstance()->hasIdentity() == FALSE) {
            $this->_helper->redirector('index' ,'index');
        }
    }

    public function indexAction() {
        // action body
    }

    public function sendNewsletterAction() {
        // action body
    }

    public function newsletterListAction() {
        //ta ut en lista med nyhetsbrev
        $newsletterModel = new Application_Model_DbTable_Newsletter();
        $this->view->newsletters = $newsletterModel->fetchAll();
    }

    public function templateListAction() {
        // action body
    }

    public function addNewsletterAction() {
        $request = $this->getRequest();
        $form = new Application_Form_NewNewsletter();
        $this->view->form = $form;

        if ($request->isPost() && $form->isValid($request->getPost())) {

            //adding newsletter
            $newsletterModel = new Application_Model_DbTable_Newsletter();
            $newsletter = $newsletterModel->createRow();
            $newsletter->setFromArray($form->getValues());
            $newsletter->text = $form->getValue('body');
            $newsletter->save();
            //adding rules
            foreach ($form->getValue('rules') as $rule => $value) {

                $newsLetterHasRuleModel = new Application_Model_DbTable_NewsLetterHasNewsRule();
                $newsLetterRules = $newsLetterHasRuleModel->createRow();
                $newsLetterRules->id_rule = $value;
                $newsLetterRules->id_newsletter = $newsletter->id;

                $privateFlag = FALSE;
                if ($value == '3') {
                    $privateFlag = TRUE;
                }
                $newsLetterRules->save();
            }

            if ($privateFlag == TRUE) {
                $newsletterQueueModel = new Application_Model_DbTable_NewsLetterQueue();
                $newsletterQueue = $newsletterQueueModel->createRow();
                $newsletterQueue->id_member = '5385';
                $newsletterQueue->id_newsletter = $newsletter->id;
                $newsletterQueue->save();
                $this->_helper->redirector('index', 'admin');
            }
            //build queue
            $memberModel = new Application_Model_DbTable_Member();
            $members = $memberModel->fetchAll();
            foreach ($members as $member) {
                $redFlag = NULL;
                foreach ($form->getValues('rules') as $rule) {
                    $memberHasNewsletterModel = new Application_Model_DbTable_MemberHasNewsRule();
                    $memberRule = $memberHasNewsletterModel->fetchRow(array('id_rule = ?' => '1', 'access = ?' => '0'));
                    if (NULL != $memberRule) {
                        $redFlag = TRUE;
                    }
                }

                //if no red flag detected register the email to send out
                if ($redFlag == FALSE && $privateFlag != TRUE) {
                    $newsletterQueueModel = new Application_Model_DbTable_NewsLetterQueue();
                    $newsletterQueue = $newsletterQueueModel->createRow();
                    $newsletterQueue->id_member = $member->ID;
                    $newsletterQueue->id_newsletter = $newsletter->id;
                    $newsletterQueue->save();
                }
            }
            
            $this->_helper->redirector('index' , 'admin');
        }
    }

    public function sendMailAction() {
        $newsletterQueueModel = new Application_Model_DbTable_NewsLetterQueue();
        $newsletterQueue = $newsletterQueueModel->fetchAll(array('sent = ?' => '0'), null, '100');
        if (count($newsletterQueue) == '0') {
			echo "finns ingen kö";
            exit;
        }


        foreach ($newsletterQueue as $queue) {

            //get newsletter
            $newsletterModel = new Application_Model_DbTable_Newsletter();
            $newsLetter = $newsletterModel->fetchRow(array('id = ?' => $queue->id_newsletter));

            //get member
            $memberModel = new Application_Model_DbTable_Member();
            $member = $memberModel->fetchRow(array('ID = ?' => $queue->id_member));
            if(null != $member->epost)
            {
            //webversion
            $webversion = '<div><a href="http://news.jonasulveseth.com/webversion/index/version/'.$newsLetter->id.'">Webversion</a></div>';
            

            $subscribe = sprintf('%s This email was sent to %s to change your settings go to %s', '<hr>', $member->epost, "<a href=http://news.jonasulveseth.com/member/manage-subscription/email/$member->epost>Your member settings</a>");

            //get layout
            $layoutModel = new Application_Model_DbTable_Template();
            $layout = $layoutModel->fetchRow(array('id = ?' => $newsLetter->id_layout));

            //creating mail
            $mail = new Zend_Mail('utf-8');
            #$bodyText = "$layout->text";
            $mail->addTo($member->epost);
            $mail->setSubject($newsLetter->title);
            $mail->setFrom('news@jonasulveseth.com', 'jonasulveseth.com');
            $mail->setBodyHtml($webversion . $layout->text . $newsLetter->text . $subscribe . '</html>');

            $mail->send();
            echo "mail sent to $member->epost";
            }
            $queElement = $newsletterQueueModel->fetchRow(array('id = ?' => $queue->id));
            $queElement->sent = '1';
            $queElement->save();
                #$newsletterQueueModel->update(array('sent' => '1'), array('id' => $queue->id));
            
            
            
        }

        exit;
    }

    public function createSaltAction() {
        $memberModel = new Application_Model_DbTable_Member();
    }

    public function editNewsletterAction() {

        $newsletterId = $this->_getParam('newsletterId');
        $newsletterModel = new Application_Model_DbTable_Newsletter();
        $newsletter = $newsletterModel->fetchRow(array('id = ?' =>$newsletterId));

        $request = $this->getRequest();
        $form = new Application_Form_NewNewsletter();
        $this->view->form = $form;

        //populera innehåll i nyhetsbrevet
        $form->populate(array('body' => $newsletter->text, 'title' => $newsletter->title));

        //populera oc ta bort alla regler
        $newsLetterHasRuleModel = new Application_Model_DbTable_NewsLetterHasNewsRule();
        //existing rules
        $rules = $newsLetterHasRuleModel->fetchAll(array('id_newsletter = ?' => $newsletterId));

        $form->populate($rules->toArray());



        if ($request->isPost() && $form->isValid($request->getPost())) {

            
            $body = $form->getValue('body');
            
            //updating newsletter
            $newsletter->text = $form->getValue('body');
            $newsletter->setFromArray($form->getValues());
            $newsletter->save();
            

            
            $newsLetterHasRuleModel->delete(array('id_newsletter' => $newsletterId));

            //adding rules
            foreach ($form->getValue('rules') as $rule => $value) {

                $newsLetterRules = $newsLetterHasRuleModel->createRow();
                $newsLetterRules->id_rule = $value;
                $newsLetterRules->id_newsletter = $newsletter->id;

                $privateFlag = FALSE;
                if ($value == '3') {
                    $privateFlag = TRUE;
                }
                $newsLetterRules->save();
            }

            if ($privateFlag == TRUE) {
                $newsletterQueueModel = new Application_Model_DbTable_NewsLetterQueue();
                $newsletterQueue = $newsletterQueueModel->createRow();
                $newsletterQueue->id_member = '5385';
                $newsletterQueue->id_newsletter = $newsletter->id;
                $newsletterQueue->save();
                $this->_helper->redirector('index', 'admin');
            }

            //build queue
            $memberModel = new Application_Model_DbTable_Member();
            $members = $memberModel->fetchAll();
            foreach ($members as $member) {
                $redFlag = NULL;
                foreach ($form->getValues('rules') as $rule) {
                    $memberHasNewsletterModel = new Application_Model_DbTable_MemberHasNewsRule();
                    $memberRule = $memberHasNewsletterModel->fetchRow(array('id_rule = ?' => '1', 'access = ?' => '0'));
                    if (NULL != $memberRule) {
                        $redFlag = TRUE;
                    }
                }

                //if no red flag detected register the email to send out
                if ($redFlag == FALSE && $privateFlag != TRUE) {
                    $newsletterQueueModel = new Application_Model_DbTable_NewsLetterQueue();
                    $newsletterQueue = $newsletterQueueModel->createRow();
                    $newsletterQueue->id_member = $member->ID;
                    $newsletterQueue->id_newsletter = $newsletterId;
                    $newsletterQueue->save();
                }
            }
            
            $this->_helper->redirector('index' , 'admin');
        }
    }

}

