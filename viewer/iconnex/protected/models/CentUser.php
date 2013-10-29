<?php

/**
 * This is the model class for table "cent_user".
 *
 * The followings are the available columns in table 'cent_user':
 * @property string $userid
 * @property string $usernm
 * @property string $narrtv
 * @property string $operator_id
 * @property string $passwd
 * @property string $passwd_md5
 * @property string $emailad
 * @property string $maxsess
 * @property string $langcd
 * @property string $menucd
 */
class CentUser extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return CentUser the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'cent_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('usernm, narrtv, operator_id, passwd, passwd_md5, emailad, maxsess, langcd, menucd', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('userid, usernm, narrtv, operator_id, passwd, emailad, maxsess, langcd, menucd', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'userid' => 'Userid',
			'usernm' => 'Username',
			'narrtv' => 'Narrtv',
			'operator_id' => 'Operator',
			'passwd' => 'Passwd',
			'passwd_md5' => 'Password',
			'emailad' => 'Email id',
			'maxsess' => 'Maxsess',
			'langcd' => 'Langcd',
			'menucd' => 'Menucd',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('userid',$this->userid,true);
		$criteria->compare('usernm',$this->usernm,true);
		$criteria->compare('narrtv',$this->narrtv,true);
		$criteria->compare('operator_id',$this->operator_id,true);
		$criteria->compare('passwd',$this->passwd,true);
		$criteria->compare('passwd_md5',$this->passwd_md5,true);
		$criteria->compare('emailad',$this->emailad,true);
		$criteria->compare('maxsess',$this->maxsess,true);
		$criteria->compare('langcd',$this->langcd,true);
		$criteria->compare('menucd',$this->menucd,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}
