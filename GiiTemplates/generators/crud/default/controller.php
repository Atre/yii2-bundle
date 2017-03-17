<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use dosamigos\editable\EditableAction;
use dezmont765\yii2bundle\actions\AsJsonAction;
use dezmont765\yii2bundle\actions\CreateAction;
use dezmont765\yii2bundle\actions\DeleteAction;
use dezmont765\yii2bundle\actions\ListAction;
use dezmont765\yii2bundle\actions\MassDeleteAction;
use dezmont765\yii2bundle\actions\SelectionByAttributeAction;
use dezmont765\yii2bundle\actions\SelectionListAction;
use dezmont765\yii2bundle\actions\UpdateAction;
use yii\helpers\ArrayHelper;
/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    public $defaultAction = 'list';
    public function behaviors()
    {
        $behaviors = ArrayHelper::merge(parent::behaviors(),[
            'layout' => <?=Inflector::camelize($generator->getControllerID())?>Layout::className(),
        ]);
        return $behaviors;
    }

    public function getRoleToLayoutMap($role) {
        $map = [];
        return $map;
    }

    public function actions()
    {
        return  [
            'ajax-update' => [
                'class' => EditableAction::className(),
                'modelClass' => <?=$modelClass?>::className(),
                'forceCreate' => false
            ],
            'list' => [
                'class' => ListAction::className(),
                'model_class' => <?=$searchModelClass?>::className()
            ],
            'create' => [
                'class' => CreateAction::className(),
            ],
            'update' => [
                'class' => UpdateAction::className(),
            ],
            'delete' => [
                'class' => DeleteAction::className(),
            ],
            'mass-delete' => [
                'class' => MassDeleteAction::className(),
            ],
            'get-selection-list' => [
                'class' => SelectionListAction::className()
            ],
            'get-selection-by-attribute' => [
                'class' => SelectionByAttributeAction::className(),
            ],
            'as-json' => [
                'class' => AsJsonAction::className()
            ]
        ];
    }

    public function getModelClass() {
        return <?=$modelClass?>::className();
    }

}
