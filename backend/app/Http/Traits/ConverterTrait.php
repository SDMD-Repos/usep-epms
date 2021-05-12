<?php

namespace App\Http\Traits;

trait ConverterTrait {

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

    public function getFullName($person)
    {
        return $person['firstName'] . " " . $person['lastName'];
    }

}
