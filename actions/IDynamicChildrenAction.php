<?php
namespace dezmont765\yii2bundle\actions;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.05.2017
 * Time: 11:01
 */

interface IDynamicChildrenAction {
    public function loadChildModelsFromRequest();
    public function save();
    public function findChildModels();
    public function getModel();
    public function run($id = null);
    public function getDefaultView();

}