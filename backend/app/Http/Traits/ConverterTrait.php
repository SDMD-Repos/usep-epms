<?php

namespace App\Http\Traits;

use App\SubCategory;

trait ConverterTrait {

    private $parentSubCategories = [];

    private $targetsBasisList = [];

    public function array_any(callable $f, $data, $compareArr=array())
    {
        foreach ($data as $datum){
            if ($f($datum, $compareArr)) {
                return true;
            }
        }

        return false;
    }

    public function integerToRomanNumeral($integer)
    {
        $integer = intval($integer);
        $result = '';

        $lookup = array(
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        );

        foreach($lookup as $roman => $value) {
            $matches = intval($integer/$value);

            $result .= str_repeat($roman, $matches);

            $integer = $integer % $value;
        }

        return $result;
    }

    public function getNestedChildren($arr, $parent=null)
    {
        $out = [];

        foreach ($arr as $i => $value) {
            $arr[$i]->key = $value->id;

            if ($arr[$i]->parent_id === $parent) {

                $children = $this->getNestedChildren($arr, $arr[$i]->id);

                if (count($children)) {
                    $arr[$i]->children = $children;
                    $arr[$i]->selectable = false;
                }

                $arr[$i]->isOpen = false;

                array_push($out, $arr[$i]);
            }
        }

        return $out;
    }

    public function getParentSubCategories($parent_id, $count=1)
    {
        $parents = SubCategory::where('id', $parent_id)->get();

        foreach($parents as $key => $parent) {

            array_push($this->parentSubCategories, $parent->name);

            if($parent->parent_id !== NULL) {
                $count++;

                $this->getParentSubCategories($parent->parent_id, $count);

            }
        }

    }

    public function splitPIOffices($data, $splitCollege=0)
    {
        $offices = array();

        foreach($data as $datum) {

            $officeType = $datum->office_type_id;

            $counter = isset($offices[$officeType]) ? count($offices[$officeType]) : 0;

            if(isset($datum->personnel_id) && $datum->personnel_id !== NULL) {
                $officeId = (int)$datum->personnel_id !== 0 ? (int)$datum->personnel_id : $datum->personnel_id;
                $officeName = $datum->personnel_name;
            }else{
                $officeId = $datum->office_id;
                $officeName = $datum->office_name;
            }

            if ($datum->is_group) {
                $officeId = $datum->group->id;
                $officeName = $datum->group->name;
            }

            /*$ipcrPeriod = IpcrPeriod::select('id')->pluck('id');

            if(isset($datum->periods)){
                $ipcrPeriod = $datum->periods->pluck('id');
            }elseif(isset($datum->ipcrPeriod)) {
                $ipcrPeriod = $datum->ipcrPeriod;
            }*/

            $offices[$datum->office_type_id][$counter] = array(
                'label' => $officeName,
                'value' => $officeId,
                'cascadeTo' => $datum->cascade_to,
//                'ipcrPeriod' => $ipcrPeriod
            );

            if ($datum->is_group) {
                $offices[$datum->office_type_id][$counter]['isGroup'] = true;
            }

            /*if($splitCollege && $officeId === "allColleges") {
                $temp = explode("_", $officeId);

                $offices[$datum->office_type_id][$counter]['id'] = $officeId;
                $offices[$datum->office_type_id][$counter]['pId'] = $officeId;
                $offices[$datum->office_type_id][$counter]['acronym'] = $officeName;
            }*/

            if($datum->vp_office_id){
                $offices[$datum->office_type_id][$counter]['pId'] = $datum->vp_office_id;

                $offices[$datum->office_type_id][$counter]['acronym'] = $datum->office_name; # used for view only PIs
            }else{
                if (!$datum->is_group) {
                    $offices[$datum->office_type_id][$counter]['children'] = true;
                }
            }
        }

        return $offices;
    }

    public function getTargetsBasisList($targetsBasis)
    {
        if($targetsBasis) {
            $suggestExists = $this->array_any(function($x, $compare){
                return $x->value === $compare;
            }, $this->targetsBasisList, $targetsBasis);

            if(!$suggestExists){
                $basisObj = new \stdClass();

                $basisObj->value = $targetsBasis;

                array_push($this->targetsBasisList, $basisObj);
            }
        }
    }

    public function extractDetails($detail)
    {
        if($detail->sub_category_id) {
            $subCategory = new \stdClass();

            $subCategory->value = $detail->sub_category_id;
            $subCategory->label = $detail->subCategory->name;
        }

        if($detail->cascading_level) {
            $cascadingLevel = new \stdClass();

            $cascadingLevel->key = $detail->cascading_level;
            $cascadingLevel->label = ucwords($detail->cascading_level);
        }

        $measures = [];

        if(count($detail->measures)) {
            foreach($detail->measures as $measure) {
                $record = new \stdClass();

                $record->key = $measure->id;
                $record->label = $measure->name;

                array_push($measures, $record);
            }
        }

        return [
            'subCategory' => $subCategory ?? null,
            'cascadingLevel' => $cascadingLevel ?? "",
            'measures' => $measures
        ];
    }

    public function checkArrayObjectsValue($lists, $object, $value)
    {
        $found = false;

        foreach ($lists as $list) {
            if ($list->$object === $value) {
                $found = true;
            }
        }

        return $found;
    }

}
