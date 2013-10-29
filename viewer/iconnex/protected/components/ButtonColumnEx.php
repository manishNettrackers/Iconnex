<?php
class ButtonColumnEx extends CButtonColumn
{
  protected function renderButton($id,$button,$row,$data)
  {

    if (isset($button['visible']) && !$this->evaluateExpression($button['visible'],array('row'=>$row,'data'=>$data)))
      return;
    $label=isset($button['label']) ? $button['label'] : $id;
    $url=isset($button['url']) ? $this->evaluateExpression($button['url'],array('data'=>$data,'row'=>$row)) : '#';

//*** start of new code
    //$options=isset($button['options']) ? $button['options'] : array();
    $options=isset($button['options']) ? $this->evaluateExpression($button['options'],array('data'=>$data,'row'=>$row)) : null;

    if(!isset($options['title']))  //*** was here before
      $options['title']=$label;    //*** 
    elseif (!isset($button['label']))
      $label = $options['title'];

    $value=isset($button['value']) ? $this->evaluateExpression($button['value'],array('data'=>$data,'row'=>$row)) : null;
    if(isset($value))
    {
      echo $this->grid->getFormatter()->format($value,"raw");
    }
    $ajax=isset($button['ajax']) ? $this->evaluateExpression($button['ajax'],array('data'=>$data,'row'=>$row)) : null;
    if(isset($ajax))
    {
      if(isset($button['imageUrl']) && is_string($button['imageUrl']))
        echo CHtml::ajaxLink(CHtml::image($button['imageUrl'],$label),$url,$ajax,$options);
      else
        echo CHtml::ajaxLink($label,$url,$ajax,$options);
    }
    else
    {
//*** end of new code
  
      if(isset($button['imageUrl']) && is_string($button['imageUrl']))
        echo CHtml::link(CHtml::image($button['imageUrl'],$label),$url,$options);
      else
        echo CHtml::link($label,$url,$options);
    } //*** new
  }
}
