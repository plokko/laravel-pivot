<?php
namespace Fico7489\Laravel\Pivot\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToManyCustom extends BelongsToMany
{
    /**
     * Attach a model to the parent.
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @param  bool   $touch
     * @return void
     */
    public function attach($ids, array $attributes = [], $touch = true)
    {
        list($idsOnly, $idsAttributes) = $this->getIdsWithAttributes($ids, $attributes);

        $this->parent->fireModelEvent('pivotModifying', true, $this->getRelationName());
        $this->parent->fireModelEvent('pivotAttaching', true, $this->getRelationName(), $idsOnly, $idsAttributes);
        parent::attach($ids, $attributes, $touch);
        $this->parent->fireModelEvent('pivotAttached', false, $this->getRelationName(), $idsOnly, $idsAttributes);
        $this->parent->fireModelEvent('pivotModified', false, $this->getRelationName(), ['attached'=>$idsOnly]);
    }

    /**
     * Detach models from the relationship.
     *
     * @param  mixed  $ids
     * @param  bool  $touch
     * @return int
     */
    public function detach($ids = [], $touch = true)
    {
        list($idsOnly) = $this->getIdsWithAttributes($ids);

        $this->parent->fireModelEvent('pivotModifying', true, $this->getRelationName());
        $this->parent->fireModelEvent('pivotDetaching', true, $this->getRelationName(), $idsOnly);
        parent::detach($ids, $touch);
        $this->parent->fireModelEvent('pivotDetached', false, $this->getRelationName(), $idsOnly);
        $this->parent->fireModelEvent('pivotModified', false, $this->getRelationName(), ['detached'=>$idsOnly]);
    }

    /**
     * Update an existing pivot record on the table.
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @param  bool   $touch
     * @return int
     */
    public function updateExistingPivot($id, array $attributes, $touch = true)
    {
        list($idsOnly, $idsAttributes) = $this->getIdsWithAttributes($id, $attributes);

        $this->parent->fireModelEvent('pivotModifying', true, $this->getRelationName());
        $this->parent->fireModelEvent('pivotUpdating', true, $this->getRelationName(), $idsOnly, $idsAttributes);
        parent::updateExistingPivot($id, $attributes, $touch);
        $this->parent->fireModelEvent('pivotUpdated', false, $this->getRelationName(), $idsOnly, $idsAttributes);
        $this->parent->fireModelEvent('pivotModified', false, $this->getRelationName(), ['updated'=>$idsOnly]);
    }

    /**
     * Sync the intermediate tables with a list of IDs or collection of models.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|array  $ids
     * @param  bool   $detaching
     * @return array
     */
    public function sync($ids, $detaching = true)
    {
        $this->parent->fireModelEvent('pivotModifying', true, $this->getRelationName());
        $result = parent::sync($ids,$detaching);
        $this->parent->fireModelEvent('pivotModified', false, $this->getRelationName(), $result);
        return $result;
    }

    /**
     * Cleans the ids and ids with attributes
     * Returns an array with and array of ids and array of id => attributes
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @return array
     */
    private function getIdsWithAttributes($id, $attributes = [])
    {
        $ids = [];

        if ($id instanceof Model) {
            $ids[$id->getKey()] = $attributes;
        } elseif ($id instanceof Collection) {
            foreach ($id as $model) {
                $ids[$model->getKey()] = $attributes;
            }
        } elseif (is_array($id)) {
            foreach ($id as $key => $attributesArray) {
                if (is_array($attributesArray)) {
                    $ids[$key] = array_merge($attributes, $attributesArray);
                } else {
                    $ids[$attributesArray] = $attributes;
                }
            }
        } elseif (is_int($id) || is_string($id)) {
            $ids[$id] = $attributes;
        }

        $idsOnly = array_keys($ids);

        return [$idsOnly, $ids];
    }
}
