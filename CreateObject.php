<?php

namespace djeager\validators;


class CreateObject extends \yii\validators\Validator
{
    public $namespace = null;
    
    public $objectName = null;
    
    public $fullName = null;
    
    public $construct = [];
    
    public $attributes = null;
    
    public $validate = true;
    
    public $isArray = false;
    
    public $return = false;
    
    public $indexBy = null;
    
    
    public function validateAttribute($model, $attribute)
    {
         
    }
    protected function create($objname,$values){
            extract($p);
            
            $obj= $fullName?:$namespace.'\\'.ucfirst($objname);
            if (!class_exists($obj)){$this->addError($attribute, "Объект '$attribute' не сушествует");return false;}            
            
            $obj=new $obj($construct); 
            $obj->setAttributes($values);
            if($validate) if(!$obj->validate()) $this->addError($attribute,$obj->getErrors());
            return $obj;
    }  
    
    
    public function vObjectCreate($attribute, array $params)
    {
        if(is_object($this->$attribute)) return;
        $p=array_merge([
            'namespace'     => null, 
            'objectName'    => null,     // название обьекта arrayValue|value - название обьекта будет братся из значения массива 
            'fullName'      => null,     // полное название обьекта с пространством имен
            'construct'     => null,     // значение для конструктора
            'attributes'    => $this->$attribute,   // атрибуты для загрузки в обьект ActiveRecords
            'validate'      => true,     // валидировать?
            'isArray'       => false,    // являются ли attributes массивом обьектов
            'return'        => false,    // true|false - возвращать данный или установить как атрибут
            'indexBy'       => null,     // if isArray каким атрибутом индексировать 
            ],$params);
        extract($p);
        $arr=[];
        
        $create=function($objname,$values)use($attribute,$p){
            extract($p);
            
            $obj= $fullName?:$namespace.'\\'.ucfirst($objname);
            if (!class_exists($obj)){$this->addError($attribute, "Объект '$attribute' не сушествует");return false;}            
            
            $obj=new $obj($construct); 
            $obj->setAttributes($values);
            if($validate) if(!$obj->validate()) $this->addError($attribute,$obj->getErrors());
            return $obj;
        };    
     
        if($isArray){
            foreach($attributes as $key=>$value){
                if($objectName=='arrayValue'){
                    $objname=is_array($value)?key($value):$value;
                    $arr[$objname]=$create($objname,$value);
                }elseif($objectName=='arrayKey'){
                    $objname=$key;
                    $arr[$objname]=$create($objname,$value);
                }else{
                    $objname=$attribute;
                    $obj=$create($objname,$value);
                    $arr[$indexBy?$obj->$indexBy:$key]=$obj;
                }
            }
        }else {
            if($objectName=='value'){
                $objname=is_array($this->$attribute)?key($this->$attribute):$this->$attribute;

                $value=is_array($this->$attribute)?reset($this->$attribute):$attributes;
                $arr=$create($objname,$value);
            }else $arr=$create($attribute,$attributes);
            
        }
        
        if($return) return $arr;
        else $this->$attribute=$arr;
    }

}
