<?php

class ActiveRecord extends CActiveRecord
{
    protected function afterFind()
    {

        foreach ($this->types() as $key => $type) {
            if (is_null($this->$key))
                continue;

            switch ($type) {
                case 'Number':
                    $this->$key = (int)$this->$key;
                    break;
                case 'String':
                    $this->$key = (string)$this->$key;
                    break;
                case 'Json':
                    $this->$key = json_decode($this->$key, 1);
                    break;
                case 'Float':
                    $this->$key = floatval($this->$key);
                    break;
                case 'Timestamp':
                    $this->$key = strtotime($this->$key);
                    break;
            }

        }

        return parent::afterFind();

    }

    protected function beforeSave()
    {

//        var_dump($this);

        foreach ($this->types() as $key => $type) {

            switch ($type) {
                case 'Json':
                    $this->$key = json_encode($this->$key, 1);
                    break;
                case 'Timestamp':
                    $this->$key = date("c", $this->$key);
                    break;
            }
        }

        return parent::beforeSave();
    }

    protected function afterSave()
    {

        foreach ($this->types() as $key => $type) {

            switch ($type) {
                case 'Json':
                    $this->$key = json_decode($this->$key, 1);
                    break;
                case 'Timestamp':
                    $this->$key = strtotime($this->$key);
                    break;
            }
        }
        return parent::afterSave();
    }

    protected function types()
    {
        return [];
    }

}