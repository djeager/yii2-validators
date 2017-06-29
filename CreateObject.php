<?php

namespace djeager\validators;


class CreateObject extends \yii\validators\Validator
{
    /**
     * @var object|array $object
     * если тип = object то будет клонироватся этот объект и загружатся в него
     * если тип array будем создавать обьект с параметрами из массива
     */
    public $object = [
        'namespace' => null,    // string|null пространнство имен для создания обьекта
        'fullName' => null,     // string|null полное название обьекта с пространством имен
        'name' => null,         // string|null [value|arrayValue|arrayKey] что считать именем обьекта
        'object' => null,       // object|string|null сам обьект или стока с названием обьекта
        'property' => null,     // string|null если @var $object[object] == is object то вернем свойство из обьекта
    ];
    /**
     * @var array $construct
     * значение для конструктора
     */
    public $construct = [];


    /**
     * @var array $pushConstruct
     * список ключей values которые необходимо переместить в конструктор
     */
    public $pushConstruct = [];

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
        if ($this->isArray) {
            foreach ($model->$attribute as $key => $value) {
                if (@$this->object['name'] == 'arrayValue') {
                    $objname = is_array($value) ? key($value) : $value;
                    $arr[$objname] = $this->create($objname, $value, $model, $attribute);
                } elseif (@$this->object['name'] == 'arrayKey') {
                    $objname = $key;
                    $arr[$objname] = $this->create($objname, $value, $model, $attribute);
                } else {
                    $objname = $attribute;
                    $obj = $this->create($objname, $value, $model, $attribute);
                    $arr[$this->indexBy ? $obj->{$this->indexBy} : $key] = $obj;
                }
            }
        } else {
            if (@$this->object['name'] == 'value') {
                $objname = is_array($model->$attribute) ? key($model->$attribute) : $model->$attribute;

                //$value=is_array($model->$attribute)?reset($model->$attribute):$attributes;
                $value = is_array($model->$attribute) ? reset($model->$attribute) : $model->$attribute;

                $arr = $this->create($objname, $value, $model, $attribute);
            } else $arr = $this->create($attribute, $model->$attribute, $model, $attribute);

        }

        return $this->return ? $arr : $model->$attribute = $arr;
    }


    protected function create($objname, $values, $model, $attribute)
    {
        $construct = $this->construct;
        $obj = $this->getObject($objname, $model) ?: $model->addError($attribute, "Объект '$attribute' не найден");
        if (!is_object($obj)) return false;

        if ($this->pushConstruct) {
            $push = array_intersect_key((array)$values, array_flip($this->pushConstruct));
            $values = array_diff_key((array)$values, array_flip($this->pushConstruct));
            $construct = array_merge($this->construct, $push);
        }

        $obj->__construct($construct);
        $obj->setAttributes($values);

        if ($this->validate) if (!$obj->validate()) $model->addError($attribute, $obj->getErrors());
        return $obj;
    }

    protected function getObject($name, $model)
    {
        if (is_object($this->object)) return $this->object;

        $o = array_merge(['object' => $name], (array)$this->object);

        extract($o);
        if (is_object(@$object)) return @$property ? $object->$property : $object;
        else {
            $name = @$fullname ?: @$namespace . '\\' . ucfirst($object);
            return class_exists($name) ? new $name : false;
        }
    }
}

?>