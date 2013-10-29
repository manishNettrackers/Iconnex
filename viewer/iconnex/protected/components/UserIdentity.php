<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    private $_id;
    public function authenticate()
    {
        $record=CentUser::model()->findByAttributes(array('usernm'=>$this->username));

        // Fetch authentication details if not already found
        $success = "Y";
        if($record===null)
        {
            $this->errorCode=self::ERROR_USERNAME_INVALID;
            $success = "N";
        }
        else if(trim($record->passwd_md5)!==md5($this->password))
        {
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
            $success = "N";
        }
        else
        {
            $this->_id=trim($record->usernm);
            //$this->setState('title', $record->title);
            $this->errorCode=self::ERROR_NONE;
            $success = "Y";
        }
        $loginaudit = new LoginAudit();
        $loginaudit->login_time = date("Y-m-d H:i:s");
        $loginaudit->in_out = "I";
        $loginaudit->login_name = $this->username;
        $loginaudit->success = $success;
        $loginaudit->source_ip = $_SERVER["REMOTE_ADDR"];
        $loginaudit->source_url = $_SERVER["HTTP_REFERER"];
        $loginaudit->save();
        return !$this->errorCode;
    }
 
//    public function getId()
 //   {
  //      return $this->_id;
   // }
}
