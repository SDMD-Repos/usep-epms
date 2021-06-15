<?php

namespace App\Http\Controllers;

use App\CascadingLevel;
use App\Category;
use App\Form;
use App\Http\Requests\StoreCategory;
use App\Http\Requests\StoreMeasure;
use App\Http\Requests\StoreProgram;
use App\Http\Requests\StoreSignatory;
use App\Http\Requests\StoreSubCategory;
use App\Http\Traits\OfficeTrait;
use App\Measure;
use App\MeasureItem;
use App\Program;
use App\Signatory;
use App\SignatoryType;
use App\SubCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    use OfficeTrait;

    private $login_user;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware(function ($request, $next) {
            $this->login_user = Auth::user();

            if ($this->login_user) {
                $this->login_user->fullName = $this->login_user->firstName . " " . $this->login_user->lastName;
            }

            return $next($request);
        });

    }

    public function getFunctions()
    {
        $categories = Category::select("*", "id as key")->orderBy('order', 'ASC')->get();

        foreach ($categories as $key => $category) {
            $categories[$key]['header'] = $this->integerToRomanNumeral($category->order) . ". " . mb_strtoupper($category->name);
        }

        return response()->json([
            'categories' => $categories
        ], 200);
    }

    public function createFunction(StoreCategory $request)
    {
        try{
            $validated = $request->validated();

            DB::beginTransaction();

            $id = str_replace(" ", "_", strtolower($validated['name']));

            $order = Category::max('order');

            $category = new Category();

            $category->id = $id;
            $category->name = $validated['name'];
            $category->percentage = $validated['percentage'];
            $category->order = ++$order;
            $category->create_id = $this->login_user->pmaps_id;
            $category->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if(!$category->save()) {
                DB::rollBack();
            }

            DB::commit();

            return response()->json([
                'success' => 'Category created successfully'
            ], 200);
        } catch (\Exception $e) {

            return response()->json($e->getMessage(), 400);
        }
    }

    public function deleteCategory($id)
    {
        try {

            $category = Category::find($id);

            $category->modify_id = $this->login_user->pmaps_id;
            $category->updated_at = Carbon::now();
            $category->history = $category->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if (!$category->save()) {
                DB::rollBack();
            } else {
                if (!$category->delete()) {
                    DB::rollBack();
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Function deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function getSubCategories()
    {
        $subCategories = SubCategory::with('category')->get();

        $modSubCategories = $this->getNestedChildren($subCategories);

        return response()->json([
            'subCategories' => $modSubCategories
        ], 200);
    }
    public function createSubCategory(StoreSubCategory $request)
    {
        try {

            $validated = $request->validated();

            DB::beginTransaction();

            $subcategory = new SubCategory();

            $subcategory->name = $validated['name'];
            $subcategory->category_id = $validated['category_id'];
            $subcategory->parent_id = $validated['parentId'];
            $subcategory->create_id = $this->login_user->pmaps_id;
            $subcategory->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if (!$subcategory->save()) {
                DB::rollBack();
            }

            DB::commit();

            return response()->json([
                'success' => 'Sub category created successfully'
            ], 200);

        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }


    public function deleteSubCategory($id)
    {
        try {

            DB::beginTransaction();

            $subcategory = SubCategory::where('id', $id)->with('childSubCategories')->get();

            foreach ($subcategory as $sub) {
                $sub->modify_id = $this->login_user->pmaps_id;
                $sub->updated_at = Carbon::now();
                $sub->history = $sub->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                if (!$sub->save()) {
                    DB::rollBack();
                } else {
                    if (!$sub->delete()) {
                        DB::rollBack();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Sub category deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function getPrograms()
    {
        $programs = Program::select("*", "id as key")->with('category')->get();

        return response()->json([
            'programs' => $programs
        ], 200);
    }

    public function createProgram(StoreProgram $request)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $program = new Program();

            $program->name = $validated['name'];
            $program->category_id = $validated['category_id'];
            $program->percentage = $validated['percentage'];
            $program->create_id = $this->login_user->pmaps_id;
            $program->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if (!$program->save()) {
                DB::rollBack();
            }

            DB::commit();

            return response()->json([
                'success' => 'Program created successfully'
            ], 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function deleteProgram($id)
    {
        try {

            $program = Program::find($id);

            $program->modify_id = $this->login_user->pmaps_id;
            $program->updated_at = Carbon::now();
            $program->history = $program->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if (!$program->save()) {
                DB::rollBack();
            } else {
                if (!$program->delete()) {
                    DB::rollBack();
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Program deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function getMeasures()
    {
        $measures = Measure::select('id', 'name', 'id as key')->with('items')->get();

        return response()->json([
            'measures' => $measures
        ], 200);
    }

    public function createMeasure(StoreMeasure $request)
    {
        try {

            $validated = $request->validated();

            DB::beginTransaction();

            $measure = new Measure;

            $measure->name = $validated['name'];
            $measure->create_id = $this->login_user->pmaps_id;
            $measure->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if ($measure->save()) {
                foreach ($validated['items'] as $item) {
                    $measureItem = new MeasureItem();

                    $measureItem->measure_id = $measure->id;
                    $measureItem->rate = (int)$item['rate'];
                    $measureItem->description = $item['description'];
                    $measureItem->create_id = $this->login_user->pmaps_id;
                    $measureItem->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                    if (!$measureItem->save()) {
                        DB::rollBack();
                    }
                }
            } else {
                DB::rollBack();
            }

            DB::commit();

            return response()->json([
                'success' => 'Measure created successfully'
            ], 200);

        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function updateMeasure(StoreMeasure $request, $id)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $measure = Measure::find($id);

            $changes = '';
            if ($measure->name != $validated['name']) {
                $changes = " from '" . $measure->name . "' to '" . $validated['name'] . "'";
            }

            $measure->history = $measure->history . "Updated " . Carbon::now() . $changes . " by " . $this->login_user->fullName . "\n";
            $measure->name = $validated['name'];
            $measure->modify_id = $this->login_user->pmaps_id;
            $measure->updated_at = Carbon::now();

            if ($measure->save()) {
                foreach ($validated['items'] as $item) {
                    if (isset($item['status']) && $item['status'] === 'new') {
                        $measureItem = new MeasureItem();

                        $measureItem->measure_id = $id;
                        $measureItem->rate = (int)$item['rate'];
                        $measureItem->description = $item['description'];
                        $measureItem->create_id = $this->login_user->pmaps_id;
                        $measureItem->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                        if (!$measureItem->save()) {
                            DB::rollBack();
                        }
                    }
                }
            } else {
                DB::rollBack();
            }

            if (!empty($deleted = $request->get('deleted'))) {
                foreach ($deleted as $delete_id) {
                    $measureItem = MeasureItem::find($delete_id);

                    $measureItem->modify_id = $this->login_user->pmaps_id;
                    $measureItem->updated_at = Carbon::now();
                    $measureItem->history = $measureItem->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                    if (!$measureItem->save()) {
                        DB::rollBack();
                    } else {
                        if (!$measureItem->delete()) {
                            DB::rollBack();
                        }
                    }
                }
            }

            DB:: commit();

            return response()->json([
                'success' => 'Measure updated successfully'
            ], 200);

        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function deleteMeasure($id)
    {
        try {

            DB::beginTransaction();

            $measure = Measure::find($id);

            $measure->modify_id = $this->login_user->pmaps_id;
            $measure->updated_at = Carbon::now();
            $measure->history = $measure->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if (!$measure->save()) {
                DB::rollBack();
            } else {
                if (!$measure->delete()) {
                    DB::rollBack();
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Measure deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function getAllForms()
    {
        $forms = Form::orderBy('ordering', 'ASC')->get();

        return response()->json([
            'spmsForms' => $forms
        ], 200);
    }

    public function getAllSignatoryTypes()
    {
        $signatoryTypes = SignatoryType::all();

        return response()->json([
            'signatoryTypes' => $signatoryTypes
        ], 200);
    }

    public function getYearSignatories($year, $formId)
    {
        $signatories = Signatory::select("*", "id as key", DB::raw("CONCAT(office_name, '_', office_id) as officeId"),
            DB::raw("CONCAT(personnel_name, '_', personnel_id) as personnelId"))->where([
            ['form_id', $formId],
            ['year', $year]
        ])->get();

        return response()->json([
            'signatories' => $signatories
        ], 200);
    }

    public function saveSignatories(StoreSignatory $request)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $year = $validated['year'];

            $typeId = $validated['typeId'];

            $formId = $validated['formId'];

            $signatories = $validated['signatories'];

            foreach($signatories as $signatory) {

                if(strpos($signatory['officeId'], '_') !== false) {
                    list($officeName, $officeId) = explode('_', $signatory['officeId']);
                } else {
                    $officeId = null;
                    $officeName = $signatory['officeId'];
                }

                if(strpos($signatory['personnelId'], '_') !== false) {
                    list($personnelName, $personnelId) = explode('_', $signatory['personnelId']);
                } else {
                    $personnelId = null;
                    $personnelName = $signatory['personnelId'];
                }

                $newSignatory = new Signatory();

                $newSignatory->year = $year;
                $newSignatory->type_id = $typeId;
                $newSignatory->form_id = $formId;
                $newSignatory->personnel_id = $personnelId;
                $newSignatory->personnel_name = $personnelName;
                $newSignatory->office_id = $officeId;
                $newSignatory->office_name = $officeName;
                $newSignatory->position = $signatory['position'];
                $newSignatory->create_id = $this->login_user->pmaps_id;
                $newSignatory->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                if(!$newSignatory->save()) {
                    DB::rollBack();
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Signatory created successfully'
            ], 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function updateSignatories(StoreSignatory $request)
    {
        try {
            $validated = $request->validated();

            $year = $validated['year'];

            $typeId = $validated['typeId'];

            $formId = $validated['formId'];

            $signatories = $validated['signatories'];

            DB::beginTransaction();

            $history = "";

            foreach($signatories as $signatory) {
                $check = Signatory::find($signatory['id']);

                list($officeName, $officeId) = explode('_', $signatory['officeId']);

                list($personnelName, $personnelId) = explode('_', $signatory['personnelId']);

                if($check) {
                    $original = $check->getOriginal();

                    if ($check->office_id !== $officeId) {
                        $check->office_id = $officeId;
                        $check->office_name = $officeName;
                        $history .= "Updated office from '" . $original['office_name'] . "' to '" . $officeName . "' " . Carbon::now() . " by " . $this->login_user->fullName . "\n";
                    }

                    if($check->personnel_id !== $personnelId) {
                        $check->personnel_id = $personnelId;
                        $check->personnel_name = $personnelName;
                        $history .= "Updated personnel from '" . $original['personnel_name'] . "' to '" . $personnelName . "' " . Carbon::now() . " by " . $this->login_user->fullName . "\n";
                    }

                    if($check->position !== $signatory['position']) {
                        $check->position = $signatory['position'];
                        $history .= "Updated position from '" . $original['position'] . "' to '" . $signatory['position'] . "' " . Carbon::now() . " by " . $this->login_user->fullName . "\n";
                    }

                    if($history !== '') {
                        $check->modify_id = $this->login_user->pmaps_id;
                        $check->updated_at = Carbon::now();
                        $check->history = $check->history . $history;
                    }

                    if(!$check->save()) {
                        DB::rollBack();
                    }
                } else {
                    $newSignatory = new Signatory();

                    $newSignatory->year = $year;
                    $newSignatory->position_id = $typeId;
                    $newSignatory->form_id = $formId;
                    $newSignatory->personnel_id = $personnelId;
                    $newSignatory->personnel_name = $personnelName;
                    $newSignatory->office_id = $officeId;
                    $newSignatory->office_name = $officeName;
                    $newSignatory->position = $signatory['position'];
                    $newSignatory->create_id = $this->login_user->pmaps_id;
                    $newSignatory->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                    if(!$newSignatory->save()) {
                        DB::rollBack();
                    }
                }

            }

            DB::commit();

            return response()->json([
                'success' => 'Signatory updated successfully'
            ], 200);

        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function deleteSignatory($id)
    {
        try {
            DB::beginTransaction();

            $signatory = Signatory::find($id);

            $signatory->modify_id = $this->login_user->pmaps_id;
            $signatory->updated_at = Carbon::now();
            $signatory->history = $signatory->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if (!$signatory->save()) {
                DB::rollBack();
            } else {
                if (!$signatory->delete()) {
                    DB::rollBack();
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Signatory deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function getAllCascadingLevels()
    {
        $cascadingLevels = CascadingLevel::orderBy('ordering', 'ASC')->get();

        return response()->json([
            'cascadingLevels' => $cascadingLevels
        ], 200);
    }
}
