<?php

/**
 * This is the model class for table "subscription".
 *
 * The followings are the available columns in table 'subscription':
 * @property integer $subscription_id
 * @property integer $subscriber_id
 * @property string $subscription_type
 * @property  $creation_time
 * @property datetime year to second $start_time
 * @property datetime year to second $end_time
 * @property datetime year to second $subscribed_time
 * @property integer $update_interval
 * @property integer $max_departures
 * @property integer $display_thresh
 * @property integer $request_id
 * @property string $disabled
 * @property string $subscription_ref
 */
class Subscription extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Subscription the static model class
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
		return 'subscription';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('subscriber_id, subscription_type, update_interval', 'required'),
			array('subscriber_id, update_interval, max_departures, display_thresh, request_id', 'numerical', 'integerOnly'=>true),
			array('creation_time, start_time, end_time, subscribed_time, disabled, subscription_ref', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('subscription_id, subscriber_id, subscription_type, creation_time, start_time, end_time, subscribed_time, update_interval, max_departures, display_thresh, request_id, disabled, subscription_ref', 'safe', 'on'=>'search'),
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
			'subscription_id' => 'Subscription',
			'subscriber_id' => 'Subscriber',
			'subscription_type' => 'Subscription Type',
			'creation_time' => 'Creation Time',
			'start_time' => 'Start Time',
			'end_time' => 'End Time',
			'subscribed_time' => 'Subscribed Time',
			'update_interval' => 'Update Interval',
			'max_departures' => 'Max Departures',
			'display_thresh' => 'Display Thresh',
			'request_id' => 'Request',
			'disabled' => 'Disabled',
			'subscription_ref' => 'Subscription Ref',
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

		$criteria->compare('subscription_id',$this->subscription_id);
		$criteria->compare('subscriber_id',$this->subscriber_id);
		$criteria->compare('subscription_type',$this->subscription_type,true);
		$criteria->compare('creation_time',$this->creation_time);
		$criteria->compare('start_time',$this->start_time);
		$criteria->compare('end_time',$this->end_time);
		$criteria->compare('subscribed_time',$this->subscribed_time);
		$criteria->compare('update_interval',$this->update_interval);
		$criteria->compare('max_departures',$this->max_departures);
		$criteria->compare('display_thresh',$this->display_thresh);
		$criteria->compare('request_id',$this->request_id);
		$criteria->compare('disabled',$this->disabled,true);
		$criteria->compare('subscription_ref',$this->subscription_ref,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}