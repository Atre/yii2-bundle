<?php
namespace dezmont765\yii2bundle\actions;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.05.2017
 * Time: 11:22
 */
interface IDynamicChildrenProcessor
{
    public function findChildModels($parent_model);


    public function loadChildModelsFromRequest();
    public function afterLoadChildModels();


    public function saveChildModels($parent_model);
}