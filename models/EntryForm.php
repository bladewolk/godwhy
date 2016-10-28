<?php

namespace app\models;

use yii\base\Model;

class EntryForm extends Model
{
    public $file;

    public function rules()
    {
        return [
            ['file', 'required', 'message' => 'Oops'],
            [['file'], 'file']
        ];
    }
}