<?php

/**
 * Class UserController
 *
 * @property Custom_View $view
 */
class UserController extends Zend_Controller_Action
{


    public function indexAction()
    {

        $this->view->headTitle('Users');

        $this->view->assign(
            'userList',
            Core_Model_Factory::get('User')->fetchAll(array(), array('timestampAdd' => 'desc'))
        );
    }
    public function itemAction()
    {

        /** @var Model_User_Entity $userEntity */
        $userEntity = Core_Model_Factory::get('User')->find($this->getParam('id'));
        $this->view->assign('userEntity', $userEntity);
        $this->view->headTitle($userEntity->name);
    }

    public function addAction()
    {
        $this->view->headTitle('Add user');
    }

    public function editAction()
    {
        $this->view->headTitle('Edit user');

        /** @var Model_User_Entity $userEntity */
        $userEntity = Core_Model_Factory::get('User')->find($this->getParam('id'));
        $this->view->assign('userEntity', $userEntity);
        $this->view->headTitle($userEntity->name);
    }

}

