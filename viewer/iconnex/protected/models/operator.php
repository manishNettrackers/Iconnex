<?php

/**
 * This is the model class for table "operator".
 *
 * The followings are the available columns in table 'operator':
 * @property integer $operator_id
 * @property string $operator_code
 * @property string $legal_name
 * @property string $address01
 * @property string $address02
 * @property string $address03
 * @property string $address04
 * @property string $short_name
 * @property string $loc_prefix
 * @property string $tel_travel
 * @property string $tel_enquiry
 */
class operator extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @return Operator the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'operator';
    }

    public function getAllOps() {
        
        $criteria = new CDbCriteria;
        $criteria->select = '*';  // only select the 'title' column
        $criteria->condition = 'legal_name=:legal_name';
        $criteria->params = array(':legal_name' => 'Arriva');

        return self::model()->find($criteria);
        
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    /*  public function attributeLabels() {
      return array(
      'legal_name' => 'legal_name',
      );
      } */

    /* public function relations() {

      return array(
      'routes' => array(self::MANY_MANY, 'Route', 'route_visibility(route_id,route_id)'),
      );
      } */
}

?>