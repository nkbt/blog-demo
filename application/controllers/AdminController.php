<?php

/**
 * Class AdminController
 *
 * @property Custom_View $view
 */
class AdminController extends Zend_Controller_Action
{


    public function indexAction()
    {

        $this->view->headTitle('Admin');
    }


    public function clearCacheAction()
    {

        $this->view->headTitle('Clear cache');

        if ($this->getRequest()->isPost()) {
            $redis = Zend_Registry::get('Redis');
            $keys = $redis->keys('Zend_Cache:*');
            $redis->del($keys);

            $this->_helper->FlashMessenger->addMessage('<p class="text-success">Cache cleared</p>');
        }

    }


    public function updateCountsAction()
    {

        $this->view->headTitle('Update counts');

        $entities = array(
            'Topic',
            'User',
            'Comment',
        );

        if ($this->getRequest()->isPost()) {
            foreach ($entities as $entityName) {
                $this->_helper->FlashMessenger->addMessage('<p class="text-success">Counts for ' . $entityName . ' successfully updated</p>');
                Core_Model_Factory::get($entityName)->updateCounts();
            }
        }

    }

}

