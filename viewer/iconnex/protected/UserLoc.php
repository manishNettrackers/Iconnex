<?php

/**
 * This is the model class for table "user_loc".
 *
 * The followings are the available columns in table 'user_loc':
 * @property integer $user_id
 * @property integer $location_id
 *
 * The followings are the available model relations:
 * @property CentUser $user
 * @property Location $location
 */
class UserLoc extends CActiveRecord
{
    public $location_code;
    public $loc_desc;

	/**
	 * Returns the static model of the specified AR class.
	 * @return UserLoc the static model class
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
		return 'user_loc';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, location_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, location_id', 'safe', 'on'=>'search'),
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
			'user' => array(self::BELONGS_TO, 'CentUser', 'userid'),
			'location' => array(self::BELONGS_TO, 'Location', 'location_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id' => 'User',
			'location_id' => 'Location',
			'loc_desc' => 'Description',
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

		$criteria = new CDbCriteria;

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('location_id',$this->location_id);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

    public function afterConstruct()
    {
        if (isset($this->location))
            $this->loc_desc = $this->location->location_code . ' ' . $this->location->description;

        parent::afterConstruct();
    }

    public function afterFind()
    {
        if (isset($this->location))
            $this->loc_desc = $this->location->location_code . ' ' . $this->location->description;

        parent::afterFind();
    }
}

