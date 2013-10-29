<?php
    class messageWindow extends GolapWidget
    {
        public function init(){
            parent::init();
            //Yii::app()->getClientScript()->registerScriptFile($this->_assetsUrl.'/messageWindow.js');
        }

        public function run(){
            $this->render('messageWindow');
        }
    }
