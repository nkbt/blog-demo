<?php

/**
 * Class TopicController
 *
 * @property Custom_View $view
 */
class TopicController extends Zend_Controller_Action
{


    public function indexAction()
    {

        $this->view->headTitle('Topics');

        $this->view->assign(
            'topicList',
            Core_Model_Factory::get('Topic')->fetchAll(array(), array('timestampAdd' => 'desc'))
        );
    }


    public function itemAction()
    {


        $y = 123;
        $x1 = 5;
        $z = $this->getRequest();

        echo $x;
        /** @var Model_Topic_Entity $topicEntity */
        $topicEntity = Core_Model_Factory::get('Topic')->find($this->getParam('id'));
        $this->view->assign('topicEntity', $topicEntity);
        $this->view->headTitle($topicEntity->title);
    }

    public function addAction()
    {
        $this->view->headTitle('Add topic');
    }

    public function editAction()
    {
        $this->view->headTitle('Edit topic');

        /** @var Model_Topic_Entity $topicEntity */
        $topicEntity = Core_Model_Factory::get('Topic')->find($this->getParam('id'));
        $this->view->assign('topicEntity', $topicEntity);
        $this->view->headTitle($topicEntity->title);
    }

}

