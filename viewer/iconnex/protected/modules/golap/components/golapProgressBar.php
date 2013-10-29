<?php

Yii::import('zii.widgets.jui.CJuiProgressBar');

class golapProgressBar extends CJuiProgressBar
{ 
        public function init ()
        {
                $this->value = "500";
                //$am = Yii::app()->assetManager;
                //$base = YiiBase::getPathOfAlias(
                        //'application.extensions.jquery-ui-custom'
                //);
                //$this->scriptUrl = rtrim($am->publish($base . '/js'), '/');
                //$this->themeUrl = rtrim($am->publish($base . '/css'), '/');
                //$this->theme = 'custom-theme';
                //$this->scriptFile = 'jquery-ui-1.8.16.custom.min.js';
                //$this->cssFile = 'jquery-ui-1.8.16.custom.css';
                parent::init();

                
        }

        public function run(){
            parent::run();
        }
}
