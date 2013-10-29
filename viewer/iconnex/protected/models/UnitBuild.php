<?php

/**
 * This is the model class for table "unit_build".
 *
 * The followings are the available columns in table 'unit_build':
 * @property integer $build_id
 * @property integer $operator_id
 * @property string $build_code
 * @property string $unit_type
 * @property string $description
 * @property integer $build_parent
 * @property string $build_status
 * @property integer $version_id
 * @property string $build_notes1
 * @property string $build_notes2
 * @property string $build_type
 * @property interval hour to second $allow_logs
 * @property interval hour to second $allow_publish
 */
class UnitBuild extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return UnitBuild the static model class
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
		return 'unit_build';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('operator_id, build_parent, version_id', 'numerical', 'integerOnly'=>true),
			array('build_code, unit_type, description, build_status, build_notes1, build_notes2, build_type, allow_logs, allow_publish', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('build_id, operator_id, build_code, unit_type, description, build_parent, build_status, version_id, build_notes1, build_notes2, build_type, allow_logs, allow_publish', 'safe', 'on'=>'search'),
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
            'vehicle'=>array(self::BELONGS_TO, 'Vehicle', 'build_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'build_id' => 'Build',
			'operator_id' => 'Operator',
			'build_code' => 'Build Code',
			'unit_type' => 'Unit Type',
			'description' => 'Description',
			'build_parent' => 'Build Parent',
			'build_status' => 'Build Status',
			'version_id' => 'Version',
			'build_notes1' => 'Build Notes1',
			'build_notes2' => 'Build Notes2',
			'build_type' => 'Build Type',
			'allow_logs' => 'Allow Logs',
			'allow_publish' => 'Allow Publish',
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

		$criteria->compare('build_id',$this->build_id);
		$criteria->compare('operator_id',$this->operator_id);
		$criteria->compare('build_code',$this->build_code,true);
		$criteria->compare('unit_type',$this->unit_type,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('build_parent',$this->build_parent);
		$criteria->compare('build_status',$this->build_status,true);
		$criteria->compare('version_id',$this->version_id);
		$criteria->compare('build_notes1',$this->build_notes1,true);
		$criteria->compare('build_notes2',$this->build_notes2,true);
		$criteria->compare('build_type',$this->build_type,true);
		$criteria->compare('allow_logs',$this->allow_logs);
		$criteria->compare('allow_publish',$this->allow_publish);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}
