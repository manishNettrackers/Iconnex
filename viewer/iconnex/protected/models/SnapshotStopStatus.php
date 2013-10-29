<?php

class SnapshotStopStatus extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Buses the static model class
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
		return 'snapshot_stop_status';
	}
        
        public function relations() {
            
            return array(
              'RoutesVisibility' =>array(self::MANY_MANY,'route_visibility','route_id'),
                
            );
        }
}
?>
