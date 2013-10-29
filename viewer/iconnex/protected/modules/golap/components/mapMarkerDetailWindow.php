<?php
    class mapMarkerDetailWindow extends GolapWidget
    {
        public function init(){
            parent::init();
            //Yii::app()->getClientScript()->registerScriptFile($this->_assetsUrl.'/mapMarkerDetailWindow.js');
        }

        public function run(){
            $this->render('mapMarkerDetailWindow');
        }
    }
