<?php

/**
 * This is the model class for table "vehicle".
 *
 * The followings are the available columns in table 'vehicle':
 * @property string $vehicle_id
 * @property string $vehicle_code
 * @property string $vehicle_type_id
 * @property string $operator_id
 * @property string $vehicle_reg
 * @property string $orun_code
 * @property string $vetag_indicator
 * @property string $modem_addr
 * @property string $build_id
 * @property string $wheelchair_access
 */
class Vehicle extends CActiveRecord
{
    public $vehicle_id;
    public $vehicle_code = "DEF";
    public $vehicle_type_id;
    public $vehicle_type_code;
    public $operator_id;
    public $vehicle_reg;
    public $orun_code;
    public $vetag_indicator;
    public $modem_addr;
    public $build_id;
    public $build_code;
    public $operator_code;
    public $wheelchair_access;
	/**
	 * Returns the static model of the specified AR class.
	 * @return Vehicle the static model class
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
		return 'vehicle';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('vehicle_code, vehicle_type_id, operator_id, vehicle_reg, orun_code, vetag_indicator, modem_addr, build_code, build_id, wheelchair_access', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('vehicle_code, vehicle_type_code, operator_code, vehicle_reg, orun_code, vetag_indicator, modem_addr, build_code, build_id, wheelchair_access', 'safe', 'on'=>'search'),
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
        'unit_build'=>array(self::HAS_ONE, 'UnitBuild', 'build_id'),
        'vehicle_type'=>array(self::HAS_ONE, 'VehicleType', 'vehicle_type_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'vehicle_id' => 'Vehicle',
			'vehicle_code' => 'Vehicle Code',
			'vehicle_type_id' => 'Vehicle Type',
			'operator_id' => 'Operator',
			'vehicle_reg' => 'Vehicle Reg',
			'orun_code' => 'Orun Code',
			'vetag_indicator' => 'Vetag Indicator',
			'modem_addr' => 'Modem Addr',
			'build_id' => 'Build',
			'build_code' => 'Build Code',
			'operator_code' => 'Operator Code',
			'wheelchair_access' => 'Wheelchair Access',
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



        /*
		$criteria->compare('vehicle_id',$this->vehicle_id,true);
		$criteria->compare('build_code',$this->build_code,true);
		$criteria->compare('vehicle_code',$this->vehicle_code,true);
		$criteria->compare('vehicle_type_id',$this->vehicle_type_id,true);
		$criteria->compare('operator_id',$this->operator_id,true);
		$criteria->compare('vehicle_reg',$this->vehicle_reg,true);
		$criteria->compare('orun_code',$this->orun_code,true);
		$criteria->compare('vetag_indicator',$this->vetag_indicator,true);
		$criteria->compare('modem_addr',$this->modem_addr,true);
		$criteria->compare('build_id',$this->build_id,true);
		$criteria->compare('wheelchair_access',$this->wheelchair_access,true);
        */

		$criteria->join = ", unit_build, vehicle_type, operator";

        $criteria->condition = "unit_build.build_id = t.build_id";
        $criteria->condition .= " AND t.vehicle_type_id = vehicle_type.vehicle_type_id";
        $criteria->condition .= " AND t.operator_id = operator.operator_id";

        if ( $this->vehicle_code ) $criteria->condition .= " AND vehicle_code MATCHES '*".$this->vehicle_code."*'";
        if ( $this->build_code ) $criteria->condition .= " AND build_code MATCHES '*".$this->build_code."*'";
        if ( $this->orun_code ) $criteria->condition .= " AND orun_code MATCHES '*".$this->orun_code."*'";
        if ( $this->operator_code ) $criteria->condition .= " AND operator_code MATCHES '*".$this->orun_code."*'";
        if ( $this->operator_id ) $criteria->condition .= " AND t.operator_id = ".$this->operator_id;
        if ( $this->vehicle_type_id ) $criteria->condition .= " AND t.vehicle_type_id = ".$this->vehicle_type_id;
        if ( $this->vehicle_reg ) $criteria->condition .= " AND vehicle_reg MATCHES '*".$this->vehicle_reg."*'";
		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}
