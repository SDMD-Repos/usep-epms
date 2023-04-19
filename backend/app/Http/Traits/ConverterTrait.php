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
                }

                $arr[$i]->isOpen = false;

                $out[] = $arr[$i];
            }
        }

        return $out;
    }

    public function getParentSubCategories($parent_id, $count=1)
    {
        $parents = SubCategory::where('id', $parent_id)->get();

        foreach($parents as $key => $parent) {

            $this->parentSubCategories[] = $parent->name;

            if($parent->parent_id !== NULL) {
                $count++;

                $this->getParentSubCategories($parent->parent_id, $count);

            }
        }

    }

    public function splitPIOffices($data, $conditions=[])
    {
        $offices = array();

        $officeList = $data;

        if(isset($conditions['origin'])) {
            switch ($conditions['origin']) {
                case 'vpopcr':
                case 'opcr':
                    $officeList = $data->offices;
                    break;
            }
        }

        foreach($officeList as $datum) {
            $officeType = $datum->field->code;

            $counter = isset($offices[$officeType]) ? count($offices[$officeType]) : 0;

            if(isset($datum->personnel_id) && $datum->personnel_id) {
                $officeId = (int)$datum->personnel_id !== 0 ? (int)$datum->personnel_id : $datum->personnel_id;
                $officeName = $datum->personnel_name;
            }else{
                $officeName = $datum->office_name;

                if($datum->subunit_id) {
                    $officeId = "SUB".$datum->subunit_id;
                }else {
                    $officeId = is_numeric($datum->office_id) ? (int)$datum->office_id : $datum->office_id;
                }
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

            $cascadeTo = null;

            if(isset($conditions['origin'])) {
                switch ($conditions['origin']) {
                    case 'vpopcr-view':
                        $cascadeTo = $datum->category_id;

                        if($datum->program_id) {
                            $cascadeTo = $datum->program->category_id . '-' . $datum->program_id;
                        }/*else if($datum->other_program_id) {
                            $cascadeTo = $datum->otherProgram->category_id . '-' . $datum->other_program_id . '-opcr';
                        }*/
                        break;
                    default:
                        $cascadeTo = $datum->cascade_to;
                        break;
                }
            }

            $offices[$officeType][$counter] = array(
                'title' => $officeName,
                'value' => $officeId,
                'cascadeTo' => $cascadeTo,
//                'ipcrPeriod' => $ipcrPeriod
            );

            if ($datum->is_group) {
                $offices[$officeType][$counter]['isGroup'] = true;
            }

            /*if($splitCollege && $officeId === "allColleges") {
                $temp = explode("_", $officeId);

                $offices[$datum->office_type_id][$counter]['id'] = $officeId;
                $offices[$datum->office_type_id][$counter]['pId'] = $officeId;
                $offices[$datum->office_type_id][$counter]['acronym'] = $officeName;
            }*/

            if($datum->vp_office_id){
                if($datum->subunit_id) {
                    $offices[$officeType][$counter]['pId'] = (int)$datum->office_id;
                    $offices[$officeType][$counter]['vp_id'] = (int)$datum->vp_office_id;
                    $offices[$officeType][$counter]['is_subunit'] = 1;
                }else {
                    $offices[$officeType][$counter]['pId'] = $datum->vp_office_id;
                }

                $offices[$officeType][$counter]['acronym'] = $datum->office_name; # used for view only PIs
            }else{
                if (!$datum->is_group) {
                    $offices[$officeType][$counter]['children'] = true;
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

                $this->targetsBasisList[] = $basisObj;
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

        if($detail->program_id) {
            $program = new \stdClass();

            $program->key = $detail->program_id;
            $program->value = $detail->program_id;
            $program->label = $detail->program->name;
        }

        if($detail->cascading_level) {
            $cascadingLevel = new \stdClass();

            $cascadingLevel->key = $detail->cascading_level;
            $cascadingLevel->label = ucwords($detail->cascading->name);
        }

        $measures = [];

        if(count($detail->measures)) {
            $measures = $this->extractMeasures($detail->measures);
        }

        return [
            'subCategory' => $subCategory ?? null,
            'program' => $program ?? null,
            'cascadingLevel' => $cascadingLevel ?? "",
            'measures' => $measures,
        ];
    }

    public function extractMeasures($measures)
    {
        $list = [];

        foreach($measures as $measure) {
            $measureId = $measure->id;
            $measureName = $measure->name;

            $record = new \stdClass();

            $record->displayAsItems = $measure->display_as_items;
            $record->isCustom = $measure->is_custom;

            if($measure->is_custom) {
                $record->value = $measureId;
                $record->label = $measureName;
                $record->items = $measure->customItems->load('rating');
            }else {
                $category = $measure->pivot->category;

                $categoryId = $category->id;

                $record->value = $categoryId . "-" . $measureId;
                $record->label = $measureName . strtolower($category->numbering);
                $record->items = $category->items;
                $record->measureId = $measureId;
                $record->categoryId = $categoryId;
            }

            $record->option = clone $record;

            $list[] = $record;
        }

        return $list;
    }
}
