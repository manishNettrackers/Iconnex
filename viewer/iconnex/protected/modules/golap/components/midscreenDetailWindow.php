<?php
    class midscreenDetailWindow extends GolapWidget
    {
        public function init(){
            parent::init();
            Yii::app()->getClientScript()->registerScriptFile($this->_assetsUrl.'/midscreenDetailWindow.js');
        }


        public function run(){
            $this->render('midscreenDetailWindow');
        }
    }
