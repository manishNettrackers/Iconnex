<?php

/**
 * This is the model class for table "iconnex_application".
 *
 * The followings are the available columns in table 'iconnex_application':
 * @property integer $app_id
 * @property string $app_name
 * @property string $app_url
 * @property integer $has_map
 * @property integer $has_grid
 * @property integer $has_line
 * @property integer $has_chart
 * @property integer $has_report
 * @property integer $autorun
 * @property string $refresh_xml
 * @property integer $autorefresh
 */
class IconnexSubmenu extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return IconnexSubmenu the static model class
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
		return 'iconnex_application';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('app_name, app_url', 'required'),
			array('has_map, has_grid, has_line, has_chart, has_report, autorun, autorefresh', 'numerical', 'integerOnly'=>true),
			array('app_name', 'length', 'max'=>60),
			array('app_url', 'length', 'max'=>255),
			array('refresh_xml', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('app_id, app_name, app_url, has_map, has_grid, has_line, has_chart, has_report, autorun, refresh_xml, autorefresh', 'safe', 'on'=>'search'),
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
			'app_id' => 'App',
			'app_name' => 'App Name',
			'app_url' => 'App Url',
			'has_map' => 'Has Map',
			'has_grid' => 'Has Grid',
			'has_line' => 'Has Line',
			'has_chart' => 'Has Chart',
			'has_report' => 'Has Report',
			'autorun' => 'Autorun',
			'refresh_xml' => 'Refresh Xml',
			'autorefresh' => 'Autorefresh',
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
		$criteria->join=" inner join  iconnex_menuitem im on t.app_id=im.app_id and im.menu_id='".$_REQUEST['id']."'";
		//$criteria->compare('app_id',$this->app_id);
		$criteria->compare('app_name',$this->app_name,true);
		$criteria->compare('app_url',$this->app_url,true);
		$criteria->compare('has_map',$this->has_map);
		$criteria->compare('has_grid',$this->has_grid);
		$criteria->compare('has_line',$this->has_line);
		$criteria->compare('has_chart',$this->has_chart);
		$criteria->compare('has_report',$this->has_report);
		$criteria->compare('autorun',$this->autorun);
		$criteria->compare('refresh_xml',$this->refresh_xml,true);
		$criteria->compare('autorefresh',$this->autorefresh);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}