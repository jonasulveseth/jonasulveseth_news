<?php

class WebversionController extends Zend_Controller_Action
{
    public function indexAction()
    {
        //get version
        $version = $this->_getParam('version');
        if(null == $version){
            $this->_helper->redirector('index' , 'index');
        }
        
        $newsletterModel = new Application_Model_DbTable_Newsletter();
        $newsletter = $newsletterModel->fetchRow(array('id = ?' => $version));
        
        $this->view->newsletter = $newsletter;
    }
}
