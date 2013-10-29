<?php

/**
 * This is the model class for table "route".
 *
 * The followings are the available columns in table 'route':
 * @property integer $route_id
 * @property string $route_code
 * @property integer $operator_id
 * @property string $description
 * @property string $outbound_desc
 * @property string $inbound_desc
 */
class Route extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Route the static model class
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
		return 'route';
	}

        
        
    public function getAllRoutes() {
        
        $criteria = new CDbCriteria;
        $criteria->select = '*';  // only select the 'title' column
        $criteria->condition = 'route_id=:route_id';
        $criteria->params = array(':route_id' => 1);

        return self::model()->find($criteria);
        
    }
	/**
	 * @return array validation rules for model attributes.
	 */
	/*public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('operator_id', 'numerical', 'integerOnly'=>true),
			array('route_code, description, outbound_desc, inbound_desc', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('route_id, route_code, operator_id, description, outbound_desc, inbound_desc', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
/*	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	/*public function attributeLabels()
	{
		return array(
			'route_id' => 'Route',
			'route_code' => 'Route Code',
			'operator_id' => 'Operator',
			'description' => 'Description',
			'outbound_desc' => 'Outbound Desc',
			'inbound_desc' => 'Inbound Desc',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	/*public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('route_id',$this->route_id);
		$criteria->compare('route_code',$this->route_code,true);
		$criteria->compare('operator_id',$this->operator_id);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('outbound_desc',$this->outbound_desc,true);
		$criteria->compare('inbound_desc',$this->inbound_desc,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}*/
}