<?php

namespace App;


trait updateValidator
{
    public function validateUpdate (array $data, $model, array $fields){
        foreach ($fields as $field) {

            if (
                array_key_exists($field, $data) &&
                $model->{$field} == $data[$field]
            ) {
                unset($data[$field]);
            }
        }

        return $data;
    }
}
