<?php

/**
 * Class CommentController
 *
 * @property Custom_View $view
 */
class CommentController extends Custom_Controller_Action
{


    public function indexAction()
    {

        $this->view->headTitle('Comments');

        $this->view->assign(
            'commentList',
            Core_Model_Factory::get('Comment')->fetchAll(array(), array('timestampAdd' => 'desc'))
        );
    }

}

