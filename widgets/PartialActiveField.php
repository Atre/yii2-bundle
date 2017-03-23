<?php
namespace dezmont765\yii2bundle\widgets;

use yii\bootstrap\ActiveField;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 15.02.2016
 * Time: 20:39
 */
class PartialActiveField extends ActiveField
{
    public function begin() {
        $clientOptions = $this->getClientOptions();
        if(!empty($clientOptions)) {
            $this->form->attributes[] = $clientOptions;
        }
        return parent::begin();
    }
}