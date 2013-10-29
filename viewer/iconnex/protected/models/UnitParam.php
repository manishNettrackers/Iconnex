<?php

/**
 * This is the model class for table "unit_param".
 *
 * The followings are the available columns in table 'unit_param':
 * @property integer $build_id
 * @property integer $component_id
 * @property integer $param_id
 * @property string $param_value
 */
class UnitParam extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return UnitParam the static model class
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
		return 'unit_param';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('build_id, component_id, param_id', 'required'),
			array('build_id, component_id, param_id', 'numerical', 'integerOnly'=>true),
			array('param_value', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('build_id, component_id, param_id, param_value', 'safe', 'on'=>'search'),
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
		'parameter'=>array(self::HAS_ONE, 'Parameter', 'param_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'build_id' => 'Build',
			'component_id' => 'Component',
			'param_id' => 'Param',
			'param_value' => 'Param Value',
			'param_desc' => 'Parameter',
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
		$criteria->compare('component_id',$this->component_id);
		$criteria->compare('param_id',$this->param_id);
		$criteria->compare('param_value',$this->param_value,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Retrieves a list of components belonging to build
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function paramsForBuildSearch($build_id, $comp_id)
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->join = ", parameter";

        $criteria->condition = "parameter.param_id = t.param_id";
        $criteria->condition .= " AND t.component_id = $comp_id";
        $criteria->condition .= " AND t.build_id = $build_id";

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}
