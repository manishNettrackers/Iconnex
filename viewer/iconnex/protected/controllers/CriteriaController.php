<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class CriteriaController extends Controller {

    
    public function actionIndex() {
        
         $requestparams = requestHandler::processRequest()->getRequestParams();
                
        // $criterias = $requestparams['criteria'];
           
         $operatormodel = new operator();
         $routemodel = new Route();
       //  $form = new CActiveForm('application.views.criteria.operatorForm', $model);
      
      /*  $fields = new criteriaBuilder();
        $f = $fields->returnhtml(array($criterias));
        
         requestHandler::Log($f);*/
         
        //NEW THEME
      //  yii::app()->theme = "bootstrap";    
        
         $this->layout ="criteria";
      
         $this->render('/criteria/timeTableViewer',array('model' =>$operatormodel,'route' => $routemodel));
    }
    
}
        
?>
