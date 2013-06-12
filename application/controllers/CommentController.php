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


    public function addAction()
    {

        $form = new Model_Comment_Form_Add();


        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getAllParams())) {
                $model = Core_Model_Factory::get('Comment');

                $this->_beginTransaction();

                $entity = $model->createEntity($form->getValues());
                $model->save($entity);

                $this->_commit();

                $this->redirect(
                    $this->_getReturnPath(
                        $this->view->url(
                            array(
                                'controller' => 'topic',
                                'action'     => 'item',
                                'id'         => $entity->idTopic

                            ), 'default', true
                        )
                    )
                );
            }
        } else {
            $form->setDefaults($this->getAllParams());
        }

        $this->view->assign('form', $form);
        $this->view->headTitle('Add comment');
    }


    public function editAction()
    {

        $form = new Model_Comment_Form_Edit();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getAllParams())) {
                $model  = Core_Model_Factory::get('Comment');
                $values = $form->getValues();

                $this->_beginTransaction();

                $entity = $model->createEntity($values);
                $model->save($entity, array_keys($values));

                $this->_commit();

                $this->redirect(
                    $this->_getReturnPath(
                        $this->view->url(
                            array(
                                'controller' => 'topic',
                                'action'     => 'item',
                                'id'         => $entity->idTopic

                            ), 'default', true
                        )
                    )
                );
            }

        } else {

            /** @var Model_Comment_Entity $commentEntity */
            $commentEntity = Core_Model_Factory::get('Comment')->find($this->getParam('id'));
            $form->setDefaults($commentEntity->toArray());

            $this->view->assign('commentEntity', $commentEntity);
            $this->view->headTitle('Edit comment');
            $this->view->headTitle($commentEntity->text);
        }


        $this->view->assign('form', $form);

    }


    public function deleteAction()
    {

        if ($this->getRequest()->isPost()) {
            $id    = (int)$this->getParam('id');
            $model = Core_Model_Factory::get('Comment');
            $this->_beginTransaction();
            $entity = $model->find($id);
            $model->delete($entity);

            $this->_commit();

            $this->redirect(
                $this->_getReturnPath(
                    $this->view->url(
                        array(
                            'controller' => 'topic',
                            'action'     => 'item',
                            'id'         => $entity->idTopic

                        ), 'default', true
                    )
                )
            );

        }

    }


    public function restoreAction()
    {

        if ($this->getRequest()->isPost()) {

            $id    = (int)$this->getParam('id');
            $model = Core_Model_Factory::get('Comment');

            $this->_beginTransaction();

            $entity = $model->find($id);

            $model->restore($entity);

            $this->_commit();

            $this->redirect(
                $this->_getReturnPath(
                    $this->view->url(
                        array(
                            'controller' => 'topic',
                            'action'     => 'item',
                            'id'         => $entity->idTopic

                        ), 'default', true
                    )
                )
            );
        }
    }
}

