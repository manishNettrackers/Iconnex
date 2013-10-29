<?php
    class GolapWidget extends CWidget
    {
        protected $_assetsUrl = null;

        public function init()
        {  
            parent::init();

            if($this->_assetsUrl===null)
            {
                $this->_assetsUrl=Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('golap.components.js'));
            }
        }
    }
