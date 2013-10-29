<?php

/**
 * This is the model class for table "location".
 *
 * The followings are the available columns in table 'location':
 * @property string $location_id
 * @property string $location_code
 * @property string $gprs_xmit_code
 * @property string $point_type
 * @property string $route_area_id
 * @property string $description
 * @property string $public_name
 * @property string $receive
 * @property string $latitude_degrees
 * @property string $latitude_minutes
 * @property string $latitude_heading
 * @property string $longitude_degrees
 * @property string $longitude_minutes
 * @property string $longitude_heading
 * @property string $geofence_radius
 * @property string $pass_angle
 * @property string $gazetteer_code
 * @property string $gazetteer_id
 * @property string $place_id
 * @property string $district_id
 * @property string $arriving_addon
 * @property string $exit_addon
 * @property string $bay_no
 */
class Location extends CActiveRecord
{

    public $loc_desc;

	/**
	 * Returns the static model of the specified AR class.
	 * @return Location the static model class
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
		return 'location';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('location_code, gprs_xmit_code, point_type, route_area_id, description, public_name, receive, latitude_degrees, latitude_minutes, latitude_heading, longitude_degrees, longitude_minutes, longitude_heading, geofence_radius, pass_angle, gazetteer_code, gazetteer_id, place_id, district_id, arriving_addon, exit_addon, bay_no', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('location_id, location_code, gprs_xmit_code, point_type, route_area_id, description, public_name, receive, latitude_degrees, latitude_minutes, latitude_heading, longitude_degrees, longitude_minutes, longitude_heading, geofence_radius, pass_angle, gazetteer_code, gazetteer_id, place_id, district_id, arriving_addon, exit_addon, bay_no', 'safe', 'on'=>'search'),
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
			'location_id' => 'Location',
			'location_code' => 'Location Code',
			'gprs_xmit_code' => 'Gprs Xmit Code',
			'point_type' => 'Point Type',
			'route_area_id' => 'Route Area',
			'description' => 'Description',
			'public_name' => 'Public Name',
			'receive' => 'Receive',
			'latitude_degrees' => 'Latitude Degrees',
			'latitude_minutes' => 'Latitude Minutes',
			'latitude_heading' => 'Latitude Heading',
			'longitude_degrees' => 'Longitude Degrees',
			'longitude_minutes' => 'Longitude Minutes',
			'longitude_heading' => 'Longitude Heading',
			'geofence_radius' => 'Geofence Radius',
			'pass_angle' => 'Pass Angle',
			'gazetteer_code' => 'Gazetteer Code',
			'gazetteer_id' => 'Gazetteer',
			'place_id' => 'Place',
			'district_id' => 'District',
			'arriving_addon' => 'Arriving Addon',
			'exit_addon' => 'Exit Addon',
			'bay_no' => 'Bay No',
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

		$criteria->compare('location_id',$this->location_id,true);
		$criteria->compare('location_code',$this->location_code,true);
		$criteria->compare('gprs_xmit_code',$this->gprs_xmit_code,true);
		$criteria->compare('point_type',$this->point_type,true);
		$criteria->compare('route_area_id',$this->route_area_id,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('public_name',$this->public_name,true);
		$criteria->compare('receive',$this->receive,true);
		$criteria->compare('latitude_degrees',$this->latitude_degrees,true);
		$criteria->compare('latitude_minutes',$this->latitude_minutes,true);
		$criteria->compare('latitude_heading',$this->latitude_heading,true);
		$criteria->compare('longitude_degrees',$this->longitude_degrees,true);
		$criteria->compare('longitude_minutes',$this->longitude_minutes,true);
		$criteria->compare('longitude_heading',$this->longitude_heading,true);
		$criteria->compare('geofence_radius',$this->geofence_radius,true);
		$criteria->compare('pass_angle',$this->pass_angle,true);
		$criteria->compare('gazetteer_code',$this->gazetteer_code,true);
		$criteria->compare('gazetteer_id',$this->gazetteer_id,true);
		$criteria->compare('place_id',$this->place_id,true);
		$criteria->compare('district_id',$this->district_id,true);
		$criteria->compare('arriving_addon',$this->arriving_addon,true);
		$criteria->compare('exit_addon',$this->exit_addon,true);
		$criteria->compare('bay_no',$this->bay_no,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

    public function afterConstruct()
    {   
        $this->loc_desc = $this->location_code . ' ' . $this->description;

        parent::afterConstruct();
    }

    public function afterFind()
    {   
        $this->loc_desc = $this->location_code . ' ' . $this->description;

        parent::afterFind();
    }
}

