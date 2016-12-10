<?php
namespace dezmont765\yii2bundle\traits;

use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 01.12.2016
 * Time: 13:14
 */
trait TSearch
{
    /**
     * Creates data provider instance with search query applied
     *
     * @param ActiveQuery $query
     * @param array $additional_sorting
     * @param $default_order
     * @return ActiveDataProvider
     * @internal param array $params
     */
    public function search(ActiveQuery $query = null, array $additional_sorting = [], $default_order = null) {
        iF($query == null) {
            $query = self::find();
        }
        $data_provider = new ActiveDataProvider([
                                                    'query' => $query,
                                                    'pagination' => ['pageSize' => 15]
                                                ]);
        $data_provider->sort->attributes += $additional_sorting;
        if($default_order !== null)
            $data_provider->sort->defaultOrder = $default_order;
        return $data_provider;
    }
}