<?php

/**
 * Class TopicController
 *
 * @property Custom_View $view
 */
class TopicController extends Custom_Controller_Action
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

        /** @var Model_Topic_Entity $topicEntity */
        $topicEntity = Core_Model_Factory::get('Topic')->find($this->getParam('id'));
        $this->view->assign('topicEntity', $topicEntity);
        $this->view->headTitle($topicEntity->title);
    }


    public function addAction()
    {

        $form = new Model_Topic_Form_Add();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getAllParams())) {
                $model = Core_Model_Factory::get('Topic');

                $this->_beginTransaction();

                $entity = $model->createEntity($form->getValues());
                $model->save($entity);

                $this->_commit();

                $this->redirect(
                    $this->_getReturnPath(
                        $this->view->url(array('controller' => 'topic'), 'default', true)
                    )
                );
            }
        }

        $this->view->assign('form', $form);
        $this->view->headTitle('Add topic');
    }


    public function editAction()
    {

        $form = new Model_Topic_Form_Edit();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getAllParams())) {
                $model  = Core_Model_Factory::get('Topic');
                $values = $form->getValues();

                $this->_beginTransaction();

                $entity = $model->createEntity($values);
                $model->save($entity, array_keys($values));

                $this->_commit();

                $this->redirect(
                    $this->_getReturnPath(
                        $this->view->url(array('controller' => 'topic'), 'default', true)
                    )
                );
            }

        } else {

            /** @var Model_Topic_Entity $topicEntity */
            $topicEntity = Core_Model_Factory::get('Topic')->find($this->getParam('id'));
            $form->setDefaults($topicEntity->toArray());

            $this->view->assign('topicEntity', $topicEntity);
            $this->view->headTitle('Edit topic');
            $this->view->headTitle($topicEntity->title);
        }


        $this->view->assign('form', $form);

    }


    public function deleteAction()
    {

        if ($this->getRequest()->isPost()) {
            $id    = (int)$this->getParam('id');
            $model = Core_Model_Factory::get('Topic');

            $this->_beginTransaction();

            $model->delete($model->find($id));

            $this->_commit();

        }

        $this->redirect(
            $this->_getReturnPath(
                $this->view->url(array('controller' => 'topic'), 'default', true)
            )
        );
    }


    public function restoreAction()
    {

        if ($this->getRequest()->isPost()) {

            $id    = (int)$this->getParam('id');
            $model = Core_Model_Factory::get('Topic');

            $this->_beginTransaction();

            $model->restore($model->find($id));

            $this->_commit();
        }

        $this->redirect(
            $this->_getReturnPath(
                $this->view->url(array('controller' => 'topic'), 'default', true)
            )
        );
    }

}

