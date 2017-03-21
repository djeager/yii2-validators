<?php

namespace djeager\validators;


class CreateObject extends \yii\validators\Validator
{
    /**
     * @var string $namespace
     * название обьекта arrayValue|value - название обьекта будет братся из значения массива 
     */
    public $namespace = null;
    
    /**
     * @var string $objectName []
     * название обьекта arrayValue|value - название обьекта будет братся из значения массива
     */
    public $objectName = null;
    
    /**
     * @var string $fullName
     * полное название обьекта с пространством имен
     */
    public $fullName = null;
    
    /**
     * @var array $construct
     * значение для конструктора
     */
    public $construct = [];
    
    /**
     * @var boll $validate
     * валидировать?
     */
    public $validate = true;
    
    /**
     * @var bool $isArray
     * являются ли attribute массивом обьектов
     */
    public $isArray = false;
    
    /**
     * @var bool $return 
     * возвращать данный или установить как атрибут
     */
    public $return = false;
    
    /**
     * @var string $indexBy
     * if isArray каким атрибутом индексировать 
     */
    public $indexBy = null;
    
    
    public function validateAttribute($model, $attribute)
    {
        if($this->isArray){
            foreach($model->$attribute as $key=>$value){
                if($this->objectName=='arrayValue'){
                    $objname=is_array($value)?key($value):$value;
                    $arr[$objname]=$create($objname,$value);
                }elseif($this->objectName=='arrayKey'){
                    $objname=$key;
                    $arr[$objname]=$create($objname,$value);
                }else{
                    $objname=$attribute;
                    $obj=$this->create($objname,$value);
                    $arr[$this->indexBy?$obj->{$this->indexBy}:$key]=$obj;
                }
            }
        }else {
            if($objectName=='value'){
                $objname=is_array($this->$attribute)?key($this->$attribute):$this->$attribute;

                $value=is_array($this->$attribute)?reset($this->$attribute):$attributes;
                $arr=$create($objname,$value);
            }else $arr=$create($attribute,$attributes);
            
        }
        
        if($this->return) return $arr;
        else $model->$attribute=$arr;

         
    }
    protected function create($objname,$values){
            
            $obj= $this->fullName?:$this->namespace.'\\'.ucfirst($objname);
            if (!class_exists($obj)){$model->addError($objname, "Объект '$obj' не найден");return false;}            
            
            $obj=new $obj($this->construct); 
            $obj->setAttributes($values);
            if($this->validate) if(!$obj->validate()) $this->addError($attribute,$obj->getErrors());
            return $obj;
    }  
}
?>