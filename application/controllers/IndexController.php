<?php

/**
 * Class IndexController
 *
 * @property Custom_View $view
 */
class IndexController extends Custom_Controller_Action
{


    public function init()
    {
        /* Initialize action controller here */
    }


    public function indexAction()
    {

        $this->view->headTitle('Welcome');

    }


}

