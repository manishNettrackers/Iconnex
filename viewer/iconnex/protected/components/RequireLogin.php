<?php
class RequireLogin extends CBehavior
{
    public function attach($owner)
    {
        $owner->attachEventHandler('onBeginRequest', array($this, 'handleBeginRequest'));
    }

    public function handleBeginRequest($event)
    {
        if (Yii::app()->user->isGuest && ( !$_GET || !in_array($_GET['r'],array('site/login')))) {
            if ($_GET &&  isset ( $_GET['r'])
            && ($_GET['r'] == 'infohost/partial'
                || preg_match('/^pwi/', $_GET['r'])
                || preg_match('/^webstop/', $_GET['r']))
            )
                $i = 1;
            else
                Yii::app()->user->loginRequired();
        }
    }
}
?>
