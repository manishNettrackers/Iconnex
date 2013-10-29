<?php

/**
 * This is the model class for table "subscriber".
 *
 * The followings are the available columns in table 'subscriber':
 * @property integer $subscriber_id
 * @property string $subscriber_code
 * @property integer $user_id
 * @property string $ip_address
 * @property integer $gateway_id
 *
 * The followings are the available model relations:
 * @property CentUser $user
 * @property Subscription[] $subscriptions
 */
class Subscriber extends CActiveRecord
{
    public $usernm;
/*    public $narrtv;
    public $operator_id;
    public $passwd;
    public $passwd_md5;
    public $emailad;
    public $maxsess;
    public $langcd;
    public $menucd;
*/

	/**
	 * Returns the static model of the specified AR class.
	 * @return Subscriber the static model class
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
		return 'subscriber';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, gateway_id', 'numerical', 'integerOnly'=>true),
			array('subscriber_code, ip_address', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('subscriber_code, usernm, ip_address, gateway_id', 'safe', 'on'=>'search'),
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
			'user' => array(self::BELONGS_TO, 'CentUser', 'user_id'),
			'subscriptions' => array(self::HAS_MANY, 'Subscription', 'subscriber_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'subscriber_id' => 'Subscriber',
			'subscriber_code' => 'Subscriber Code',
			'user_id' => 'User ID',
			'usernm' => 'User',
			'ip_address' => 'Ip Address',
			'gateway_id' => 'Gateway',
		);
	}

    protected function beforeDelete()
    {
        $dbCommand = Yii::app()->db->createCommand("DELETE FROM subscription where subscriber_id = " . $this->subscriber_id)->execute();
        return parent::beforeDelete();
    }

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

        $sort = new CSort();
        $sort->attributes = array(
            'subscriber_code',
            'usernm'=>array(
                'asc'=>'cent_user.usernm',
                'desc'=>'cent_user.usernm desc',
            ),
            'ip_address',
            'gateway_id'
        );

		//$criteria->compare('subscriber_id',$this->subscriber_id);
		//$criteria->compare('subscriber_code',$this->subscriber_code,true);
		//$criteria->compare('user_id',$this->user_id);
		//$criteria->compare('ip_address',$this->ip_address,true);
		//$criteria->compare('gateway_id',$this->gateway_id);

		$criteria->join = ", cent_user";
        $criteria->condition = "t.user_id = cent_user.userid";
        if ( $this->usernm ) $criteria->condition .= " AND usernm MATCHES '*".$this->usernm."*'";
        if ( $this->subscriber_code ) $criteria->condition .= " AND subscriber_code MATCHES '*".$this->subscriber_code."*'";
        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'sort'=>$sort,
          ));
	}
}
