<?php

namespace App\Helpers;


trait updateValidator
{
    public function validateUpdate (array $data, $model, array $fields, string $prefix = null){
        foreach ($fields as $field) {
            if(!is_null($prefix)){
                $requestValue = data_get($data, $prefix.$field);

                if (
                    array_key_exists($field, $data) &&
                    $model->{$field} == $requestValue
                ) {
                    unset($data[$field]);
                }

            }else{
                if (
                    array_key_exists($field, $data) &&
                    $model->{$field} == $data[$field]
                ) {
                    unset($data[$field]);
                }
            }
        }

        return $data;
    }
}
