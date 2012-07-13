<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public function init() {
        
        $this->bootstrap('db');
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        //Initialize and/or retrieve a ViewRenderer object on demand via the helper broker
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->initView();

        //add the global helper directory path
        $viewRenderer->view->addHelperPath('/application/common/view/helpers');
    }

}

