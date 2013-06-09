<?php

class Custom_Form_Decorator_FormButtons extends Zend_Form_Decorator_Abstract
{


    public function render($content)
    {

        switch ($this->getPlacement()) {
            case self::PREPEND:
                return $this->_getMarkup() . $content;
            case self::APPEND:
            default:
                return $content . $this->_getMarkup();
        }
    }


    protected function _getMarkup()
    {

        $buttons = $this->getOption('button');
        if (empty($buttons)) {
            return '';
        }
        if (array_key_exists('tag', $buttons)) {
            $buttons = array('single' => $buttons);
        } elseif (array_key_exists('single', $buttons)) {
            $buttons = array('single' => $buttons['single']);
        }

        /** @var Custom_View $view */
        $view = $this->getElement()->getView();

        $xhtml = '';
        foreach ($buttons as $attribs) {
            if (!is_array($attribs)) {
                continue;
            }

            if (!array_key_exists('class', $attribs)) {
                $attribs['class'] = '';
            }

            switch ($attribs['tag']) {
                case 'input':
                    $type = $attribs['type'];
                    unset($attribs['tag'], $attribs['type'], $attribs['decorator'], $attribs['decorators']);

                    $helper = 'form' . ucfirst($type);
                    if ($type == 'reset') {
                        $xhtml .= $view->$helper(null, $view->translate($attribs['title']), $attribs);
                    } else {
                        $xhtml .= $view->$helper( /*$view->translate($attribs['title']) */
                            null, $view->translate($attribs['title']), $attribs
                        );
                    }
                    $xhtml .= " ";
                    break;
                case 'a':
                default:
                    $attribs['href'] = empty($attribs['href']) ? 'javascript:void(0)' : $attribs['href'];
                    $decorators      = array(
                        new Zend_Form_Decorator_HtmlTag($attribs),
                    );
                    $content         = $view->translate($attribs['title']);
                    foreach ($decorators as $decorator) {
                        $content = $decorator->render($content);
                    }
                    $xhtml .= $content . " ";
                    break;
            }
        }
        $xhtml .= '<span class="clear"></span>';
        $containerOptions = $this->getOption('container');
        if (empty($containerOptions)) {
            $containerOptions = array('tag' => 'div', 'class' => "form-buttons");
        }
        $attribs = $this->getOption('attribs');
        if (empty($attribs)) {
            $attribs = $containerOptions;
        } else {
            $attribs = array_merge($containerOptions, $attribs);
        }

        $decorator = new Zend_Form_Decorator_HtmlTag($attribs);

        return $decorator->render($xhtml) . " ";
    }
}