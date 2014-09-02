<?php

class RestActiveRecord extends CActiveRecord
{
    protected function typesCastFromDb()
    {
        foreach ($this->types() as $key => $type) {
            if (is_null($this->$key))
                continue;

            $type = strtolower($type);
            switch ($type) {
                case 'int':
                case 'number':
                    $this->$key = (int)$this->$key;
                    break;
                case 'string':
                    $this->$key = (string)$this->$key;
                    break;
                case 'json':
                case 'object':
                    $this->$key = json_decode($this->$key, 1);
                    break;
                case 'float':
                case 'double':
                    $this->$key = floatval($this->$key);
                    break;
                case 'timestamp':
                    $this->$key = strtotime($this->$key);
                    break;
            }

        }
    }

    protected function typesCastToDb()
    {
        foreach ($this->types() as $key => $type) {
            if (is_null($this->$key))
                continue;

            $type = strtolower($type);
            switch ($type) {
                case 'json':
                case 'object':
                    $this->$key = json_encode($this->$key, 1);
                    break;
                case 'timestamp':
                    $this->$key = date("c", $this->$key);
                    break;
            }
        }
    }

    protected function afterFind()
    {
        $this->typesCastFromDb();
        return parent::afterFind();

    }

    protected function beforeSave()
    {
        $this->typesCastToDb();
        return parent::beforeSave();
    }

    protected function afterSave()
    {
        $this->typesCastFromDb();
        return parent::afterSave();
    }

    protected static function types()
    {
        return [];
    }

    /**
     * need protected $_attributes
     *
     * @param string $name_key
     * @param mixed $value
     * @return bool
     */

    public function setAttribute($name_key, $value)
    {
        $pos = strpos($name_key, '[');
        if ($pos) {
            $key = substr($name_key, $pos + 1, -1);
            $name = substr($name_key, 0, $pos);
            if (property_exists($this, $name))
                $this->$name[$key] = $value;
            elseif (isset($this->getMetaData()->columns[$name]))
                $this->_attributes[$name][$key] = $value;
            else
                return false;
            return true;

        } else {
            return parent::setAttribute($name_key, $value);
        }

    }
}