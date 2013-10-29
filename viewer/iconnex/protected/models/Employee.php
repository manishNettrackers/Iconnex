<?php

/**
 * This is the model class for table "employee".
 *
 * The followings are the available columns in table 'employee':
 * @property string $operator_id
 * @property string $employee_id
 * @property string $employee_code
 * @property string $fullname
 * @property string $orun_code
 */
class Employee extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Employee the static model class
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
		return 'employee';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('operator_id, employee_code, fullname, orun_code', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('operator_id, employee_id, employee_code, fullname, orun_code', 'safe', 'on'=>'search'),
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
			'operator_id' => 'Operator',
			'employee_id' => 'Employee',
			'employee_code' => 'Employee Code',
			'fullname' => 'Fullname',
			'orun_code' => 'Orun Code',
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

        $criteria->condition = "1 = 1";
        if ( $this->operator_id ) $criteria->condition .= " AND t.operator_id = ".$this->operator_id;
        if ( $this->employee_id ) $criteria->condition .= " AND t.employee_id = ".$this->employee_id;
        if ( $this->employee_code ) $criteria->condition .= " AND t.employee_code MATCHES '*". $this->employee_code."*'";
        if ( $this->fullname ) $criteria->condition .= " AND t.fullname MATCHES '*". $this->fullname."*'";
        if ( $this->orun_code ) $criteria->condition .= " AND t.orun_code MATCHES '*". $this->orun_code."*'";

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}
