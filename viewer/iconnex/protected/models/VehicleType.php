<?php

/**
 * This is the model class for table "vehicle_type".
 *
 * The followings are the available columns in table 'vehicle_type':
 * @property integer $vehicle_type_id
 * @property string $vehicle_type_code
 * @property string $vehicle_type_desc
 * @property interval hour to second $vehicle_length
 * @property interval hour to second $seating_cap
 * @property interval hour to second $standing_cap
 * @property interval hour to second $special_cap
 */
class VehicleType extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return VehicleType the static model class
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
		return 'vehicle_type';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('vehicle_type_code, vehicle_type_desc, vehicle_length, seating_cap, standing_cap, special_cap', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('vehicle_type_id, vehicle_type_code, vehicle_type_desc, vehicle_length, seating_cap, standing_cap, special_cap', 'safe', 'on'=>'search'),
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
			'vehicle_type_id' => 'Vehicle Type',
			'vehicle_type_code' => 'Vehicle Type Code',
			'vehicle_type_desc' => 'Vehicle Type Desc',
			'vehicle_length' => 'Vehicle Length',
			'seating_cap' => 'Seating Cap',
			'standing_cap' => 'Standing Cap',
			'special_cap' => 'Special Cap',
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

		$criteria->compare('vehicle_type_id',$this->vehicle_type_id);
		$criteria->compare('vehicle_type_code',$this->vehicle_type_code,true);
		$criteria->compare('vehicle_type_desc',$this->vehicle_type_desc,true);
		$criteria->compare('vehicle_length',$this->vehicle_length);
		$criteria->compare('seating_cap',$this->seating_cap);
		$criteria->compare('standing_cap',$this->standing_cap);
		$criteria->compare('special_cap',$this->special_cap);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}