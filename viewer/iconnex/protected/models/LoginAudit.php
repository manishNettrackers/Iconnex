<?php

/**
 * This is the model class for table "login_audit".
 *
 * The followings are the available columns in table 'login_audit':
 * @property string $login_time
 * @property string $in_out
 * @property string $login_name
 * @property string $success
 * @property string $source_ip
 * @property string $source_url
 */
class LoginAudit extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return LoginAudit the static model class
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
		return 'login_audit';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('login_time, in_out, login_name, success, source_ip, source_url', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('login_time, in_out, login_name, success, source_ip, source_url', 'safe', 'on'=>'search'),
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
			'login_time' => 'Login Time',
			'in_out' => 'In Out',
			'login_name' => 'Login Name',
			'success' => 'Success',
			'source_ip' => 'Source Ip',
			'source_url' => 'Source Url',
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

		$criteria->compare('login_time',$this->login_time,true);
		$criteria->compare('in_out',$this->in_out,true);
		$criteria->compare('login_name',$this->login_name,true);
		$criteria->compare('success',$this->success,true);
		$criteria->compare('source_ip',$this->source_ip,true);
		$criteria->compare('source_url',$this->source_url,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}