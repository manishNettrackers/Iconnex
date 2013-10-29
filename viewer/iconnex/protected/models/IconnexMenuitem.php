<?php

/**
 * This is the model class for table "iconnex_menuitem".
 *
 * The followings are the available columns in table 'iconnex_menuitem':
 * @property integer $menu_id
 * @property integer $menu_no
 * @property integer $app_id
 * @property string $run_location
 */
class IconnexMenuitem extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return IconnexMenuitem the static model class
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
		return 'iconnex_menuitem';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			//array('menu_no, app_id', 'required'),
			array('menu_id, menu_no, app_id', 'numerical', 'integerOnly'=>true),
			array('run_location', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('menu_id, menu_no, app_id, run_location', 'safe', 'on'=>'search'),
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
			'menu_id' =>'Menu',
			'menu_no' => 'Menu No',
			'app_id' => 'App',
			'run_location' => 'Run Location',
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

		$criteria->compare('menu_id',$this->menu_id);
		$criteria->compare('menu_no',$this->menu_no);
		$criteria->compare('app_id',$this->app_id);
		$criteria->compare('run_location',$this->run_location,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}