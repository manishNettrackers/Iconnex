<?php

/**
 * This is the model class for table "iconnex_menu_user".
 *
 * The followings are the available columns in table 'iconnex_menu_user':
 * @property integer $user_id
 * @property integer $menu_id
 * @property integer $app_id
 * @property integer $autorun
 * @property integer $show_accordion
 * @property integer $show_buttons
 */
class IconnexMenuUser extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return IconnexMenuUser the static model class
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
		return 'iconnex_menu_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id', 'required'),
			array('user_id, menu_id, app_id, autorun, show_accordion, show_buttons', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, menu_id, app_id, autorun, show_accordion, show_buttons', 'safe', 'on'=>'search'),
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
			'user_id' => Yii::t('iconnex_menu_user','User'),
			'menu_id' => Yii::t('iconnex_menu_user','Menu'),
			'app_id' => Yii::t('iconnex_menu_user','App'),
			'autorun' => Yii::t('iconnex_menu_user','Autorun'),
			'show_accordion' => Yii::t('iconnex_menu_user','Show Accordion'),
			'show_buttons' => Yii::t('iconnex_menu_user','Show Buttons'),
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

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('menu_id',$this->menu_id);
		$criteria->compare('app_id',$this->app_id);
		$criteria->compare('autorun',$this->autorun);
		$criteria->compare('show_accordion',$this->show_accordion);
		$criteria->compare('show_buttons',$this->show_buttons);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}