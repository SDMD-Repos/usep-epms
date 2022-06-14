<?php

namespace App\Http\Controllers;

use App\CascadingLevel;
use App\Category;
use App\Form;
use App\FormAccess;
use App\FormCategory;
use App\FormField;
use App\FormFieldSetting;
use App\Group;
use App\GroupMember;
use App\Http\Requests\StoreCategory;
use App\Http\Requests\StoreFormCategory;
use App\Http\Requests\StoreFormFieldSetting;
use App\Http\Requests\StoreGroup;
use App\Http\Requests\StoreMeasure;
use App\Http\Requests\StoreProgram;
use App\Http\Requests\StoreOtherProgram;
use App\Http\Requests\StoreSignatory;
use App\Http\Requests\StoreSubCategory;
use App\Http\Requests\UpdateDefaultProgram;
use App\Http\Requests\UpdateGroup;
use App\Http\Traits\OfficeTrait;
use App\Measure;
use App\MeasureItem;
use App\Program;
use App\OtherProgram;
use App\Signatory;
use App\SignatoryType;
use App\SubCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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

    public function getFunctions($year, $formId=null)
    {
        if($formId) {
            $categories = Category::select("*", "id as key")->where('year', $year)
                ->with(['formCategory' => function($query) use ($formId) {
                    $query->where('form_id', $formId)->where('display_name', '<>', NULL);
                }])
                ->orderBy('order', 'ASC')->get();
        } else {
            $categories = Category::select("*", "id as key")->orderBy('order', 'ASC')->where('year', $year)->get();
        }

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

            $year = $validated['year'];

            DB::beginTransaction();

            $order = Category::where('year', $year)->max('order');

            $category = new Category();

            $category->name = $validated['name'];
            $category->year = $year;
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

            if((isset($category->programs) && count($category->programs)) && (isset($category->subCategory) && count($category->subCategory))) {
                return response()->json('Sorry, unable to delete '.$category->name.'. There were saved Programs and Sub Categories under this category', 409);
            }

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

    public function updateProgramFunction(UpdateDefaultProgram $request, $id)
    {
        try {
            $validated = $request->validated();

            $defaultProgram = $validated['defaultProgram'];

            DB::beginTransaction();

            $category = Category::find($id);

            $category->default_program_id = $defaultProgram['key'];
            $category->modify_id = $this->login_user->pmaps_id;
            $category->updated_at = Carbon::now();
            $category->history = $category->history . "Update Default Program " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if(!$category->save()) {
                DB::rollBack();
            }

            DB::commit();

            return response()->json("Function's default program was updated successfully", 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }

    }

    public function getSubCategories($year, $isNested=0)
    {
        $subCategories = SubCategory::with('category')->where('year', $year)->get();

        $isNested = filter_var($isNested, FILTER_VALIDATE_BOOLEAN);

        $modSubCategories = $isNested ? $this->getNestedChildren($subCategories) : $subCategories;

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
            $subcategory->year = $validated['year'];
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

    public function getPrograms($year)
    {
        $programs = Program::select("*", "id as key")->where('year', $year)->with('category')->get();

        return response()->json([
            'programs' => $programs
        ], 200);
    }

    public function getOtherPrograms($year,$form_id)
    {
        $programs = OtherProgram::select("*", "id as key")->where('year', $year)->where('form_id',$form_id)->with('category')->get();

        return response()->json([
            'otherPrograms' => $programs
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
            $program->year = $validated['year'];
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

    public function createOtherProgram(StoreOtherProgram $request)
    {

        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $program = new OtherProgram();

            $program->name = $validated['name'];
            $program->category_id = $validated['category_id'];
            $program->form_id = $validated['formId'];
            $program->percentage = $validated['percentage'];
            $program->year = $validated['year'];
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

            if($program)

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

    public function deleteOtherProgram($id)
    {
        try {

            $program = OtherProgram::find($id);

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

    public function getMeasures($year)
    {
        $measures = Measure::select('id', 'name', 'year', 'id as key', 'created_at')->where('year', $year)->with('items')->get();

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
            $measure->year = $validated['year'];
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
        $forms = Form::orderBy('ordering', 'asc')->get();

        return response()->json(['forms' => $forms], 200);
    }

    public function getUserFormAccess($pmaps_id)
    {
        $userForms = FormAccess::where(function($q) use ($pmaps_id) {
            $q->where('pmaps_id', $pmaps_id)->orWhere('staff_id', $pmaps_id);
        })->with('form')->get();

        return response()->json([
            'userForms' => $userForms
        ], 200);
    }

    public function getAllSignatoryTypes()
    {
        $signatoryTypes = SignatoryType::all();

        return response()->json([
            'signatoryTypes' => $signatoryTypes
        ], 200);
    }

    public function getYearSignatories($year, $formId, $officeId)
    {
        if($officeId === 'undefined') {
            $signatories = Signatory::select("*", "id as key")->where([
                ['form_id', $formId],
                ['year', $year]
            ])->get();
        } else {
            $signatories = Signatory::select("*", "id as key")->where([
                ['form_id', $formId],
                ['year', $year],
                ['office_form_id', $officeId]
            ])->get();
        }

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

            $officeFormId = $validated['officeId'] ?? null;

            foreach($signatories as $signatory) {

                if(!$signatory['isCustom']) {
                    $officeName = $signatory['officeId']['label'];
                    $officeId = $signatory['officeId']['value'];
                } else {
                    $officeId = null;
                    $officeName = $signatory['officeId'];
                }

                if(!$signatory['isCustom']) {
                    $personnelName = $signatory['personnelId']['label'];
                    $personnelId = $signatory['personnelId']['value'];
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
                $newSignatory->office_form_id = $officeFormId; // selected office for the signatories added
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

//                list($officeName, $officeId) = explode('_', $signatory['officeId']);


//                list($personnelName, $personnelId) = explode('_', $signatory['personnelId']);

                if(!$signatory['isCustom']) {
                    $officeName = $signatory['officeId']['label'];
                    $officeId = $signatory['officeId']['value'];
                } else {
                    $officeId = null;
                    $officeName = $signatory['officeId'];
                }

                if(!$signatory['isCustom']) {
                    $personnelName = $signatory['personnelId']['label'];
                    $personnelId = $signatory['personnelId']['value'];
                } else {
                    $personnelId = null;
                    $personnelName = $signatory['personnelId'];
                }

                if($check) {
                    $original = $check->getOriginal();

                    if ($check->office_id !== $officeId || ($signatory['isCustom'] && $check->office_name !== $officeName)) {
                        $check->office_id = $officeId;
                        $check->office_name = $officeName;
                        $history .= "Updated office from '" . $original['office_name'] . "' to '" . $officeName . "' " . Carbon::now() . " by " . $this->login_user->fullName . "\n";
                    }

                    if($check->personnel_id !== $personnelId || ($signatory['isCustom'] && $check->personnel_name !== $personnelName)) {
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

    public function getAllGroups()
    {
        $groups = Group::select('*', 'id as key')->with('members')->get();

        return response()->json([
            'groups' => $groups
        ], 200);
    }

    public function saveGroup(StoreGroup $request)
    {
        try{

            $validated = $request->validated();

            $name = $validated['name'];
            $effectivy = $validated['effectivity'];
            $supervising = $validated['supervising'];
            $members = $validated['members'];

            DB::beginTransaction();

            $group = new Group;

            $group->name = $name;
            $group->effective_until = $effectivy;
            $group->supervising_id = $supervising['value'];
            $group->supervising_name = $supervising['label'];

            if($validated['hasChair']){
                $chairId = $validated['chairId'];
                $chairOffice = $validated['chairOffice'];

                $group->oic_id = $chairId['value'];
                $group->oic_name = trim($chairId['label']);
                $group->oic_dept_id = trim($chairOffice['value']);
                $group->oic_dept_name = trim($chairOffice['label']);
            }

            $group->create_id = $this->login_user->pmaps_id;
            $group->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if($group->save()){
                foreach($members as $member) {
                    $this->saveGroupMember($group->id, $member);
                }
            }else{
                DB::rollBack();
            }

            DB::commit();

            return response()->json('Group created successfully', 200);
        }catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode() && ($e->getCode() < 511)) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function saveGroupMember($group_id, $member)
    {
        try{
            $newGroup = Group::find($group_id);

            $detail = $member['id'];
            $office = $member['officeId'];

            $newMember = new GroupMember;

            $newMember->member_id = $detail['value'];
            $newMember->member_name = trim($detail['label']);
            $newMember->office_id = $office['value'];
            $newMember->office_name = $office['label'];
            $newMember->create_id = $this->login_user->pmaps_id;
            $newMember->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if(!$newGroup->members()->save($newMember)) {
                DB::rollBack();
            }
        }catch (\Exception $e) {
            dd($e);
        }
    }

    public function updateGroup(UpdateGroup $request, $id)
    {
        try {
            $validated = $request->validated();

            $name = $validated['name'];
            $effectivity = $validated['effectivity'];
            $supervising = $validated['supervising'];

            DB::beginTransaction();

            $group = Group::find($id);

            $original = $group->getOriginal();

            $group->name = $name;
            $group->effective_until = $effectivity;
            $group->supervising_id = $supervising['value'];
            $group->supervising_name = $supervising['label'];

            if($validated['hasChair']) {
                $chairId = $validated['chairId'];
                $chairOffice = $validated['chairOffice'];

                $group->oic_id = $chairId['value'];
                $group->oic_name = trim($chairId['label']);
                $group->oic_dept_id = trim($chairOffice['value']);
                $group->oic_dept_name = trim($chairOffice['label']);
            } else {
                $group->oic_id = NULL;
                $group->oic_name = NULL;
                $group->oic_dept_id = NULL;
                $group->oic_dept_name = NULL;
            }

            $group->modify_id = $this->login_user->pmaps_id;
            $group->updated_at = Carbon::now();

            $history = '';

            if($group->isDirty('name')){
                $history .= "Updated Name from '".$original['name']."' to '".$name."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
            }

            if($group->isDirty('effective_until')){
                $history .= "Updated Effective Until from '".$original['effective_until']."' to '".$effectivity."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
            }

            if($group->isDirty('supervising_id')){
                $history .= "Updated Supervising office from '".$original['supervising_name']."' to '".$supervising['label']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
            }

            if($group->isDirty('oic_id')){
                $history .= "Updated Officer-in-Charge from '".$original['oic_name']."' to '".$group->oic_name."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
            }

            $group->history = $group->history . $history;

            if ($group->save()) {
                $members = $validated['members'];

                foreach ($members as $member) {
                    if (isset($member['status']) && $member['status'] === 'new') {
                        $this->saveGroupMember($id, $member);
                    }
                }
            } else {
                DB::rollBack();
            }

            $deleted = $validated['deleted'];

            if (!empty($deleted)) {
                foreach ($deleted as $deleteId) {
                    $deleteMember = GroupMember::find($deleteId);

                    $deleteMember->modify_id = $this->login_user->pmaps_id;
                    $deleteMember->updated_at = Carbon::now();
                    $deleteMember->history = $deleteMember->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                    if (!$deleteMember->save()) {
                        DB::rollBack();
                    } else {
                        if (!$deleteMember->delete()) {
                            DB::rollBack();
                        }
                    }
                }
            }

            DB:: commit();

            return response()->json('Group updated successfully', 200);

        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function deleteGroup($id)
    {
        try {

            DB::beginTransaction();

            $group = Group::find($id);

            $group->modify_id = $this->login_user->pmaps_id;
            $group->updated_at = Carbon::now();
            $group->history = $group->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if (!$group->save()) {
                DB::rollBack();
            } else {
                if (!$group->delete()) {
                    DB::rollBack();
                }
            }

            DB::commit();

            return response()->json('Group deleted successfully', 200);
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

    public function getAllFormFields($year, $formId)
    {
        $formFields = FormField::with(['settings' => function($query) use ($year, $formId) {
            $query->where('year', $year)->where('form_id', $formId);
        }])->get();

        return response()->json([
            'formFields' => $formFields
        ], 200);
    }

    public function saveFormFieldSettings(StoreFormFieldSetting $request)
    {
        try{
            $validated = $request->validated();

            $year = $validated['year'];
            $fieldId = $validated['fieldId'];
            $formId = $validated['formId'];
            $setting = $validated['setting'];

            DB::beginTransaction();

            $isExists = FormFieldSetting::where('year', $year)->where('field_id', $fieldId)->where('form_id', $formId)->first();

            if ($isExists) {
                return response()->json('Unable to save data. Settings has already been set', 409);
            }

            $settings = new FormFieldSetting();

            $settings->year = $year;
            $settings->field_id = $fieldId;
            $settings->form_id = $formId;
            $settings->setting = $setting['value'];
            $settings->create_id = $this->login_user->pmaps_id;
            $settings->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if(!$settings->save()) {
                DB::rollBack();
            }

            DB::commit();

            return response()->json([
                'success' => 'Form Field Setting created successfully'
            ], 200);

        }catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function updateFormFieldSettings(StoreFormFieldSetting $request, $id)
    {
        try {
            $validated = $request->validated();

            $setting = $validated['setting'];
            DB::beginTransaction();

            $settings = FormFieldSetting::find($id);

            $original = $settings->getOriginal();

            $settings->setting = $setting['value'];

            $settings->modify_id = $this->login_user->pmaps_id;
            $settings->updated_at = Carbon::now();

            $history = '';

            if($settings->isDirty('setting')){
                $history .= "Updated Setting from '".$original['setting']."' to '".$setting['value']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
            }

            $settings->history = $settings->history . $history;

            if(!$settings->save()){
                DB::rollBack();
            }

            DB::commit();

            return response()->json('Settings updated successfully', 200);

        }catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function getAllFormsByPermission(Request $data)
    {
        $forms = Form::select('*')->whereIn('id', $data)->orderBy('ordering', 'asc')->get();

        return response()->json(['forms' => $forms], 200);
    }

    public function saveFormCategory(StoreFormCategory $request)
    {
        try {
            $validated = $request->validated();

            $formId = $validated['formId'];
            $categoryId = $validated['categoryId'];
            $displayName = $validated['name'];

            $successMessage = '';

            DB::beginTransaction();

            $formCategory = FormCategory::where('form_id', $formId)->where('category_id', $categoryId)->first();

            if ($formCategory) {
                $original = $formCategory->getOriginal();

                $formCategory->display_name = $displayName;

                $formCategory->modify_id = $this->login_user->pmaps_id;
                $formCategory->updated_at = Carbon::now();

                $history = '';

                if($formCategory->isDirty('display_name')){
                    $history .= "Updated Display Name from '".$original['display_name']."' to '".$displayName."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                $formCategory->history = $formCategory->history . $history;

                $successMessage = 'Form Category was updated successfully';

            } else {
                $formCategory = new FormCategory();

                $formCategory->form_id = $formId;
                $formCategory->category_id = $categoryId;
                $formCategory->display_name = $displayName;
                $formCategory->create_id = $this->login_user->pmaps_id;
                $formCategory->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                $successMessage = 'Form Category was saved successfully';
            }

            if(!$formCategory->save()) {
                DB::rollBack();
            }

            DB::commit();

            return response()->json([
                'message' => $successMessage
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

    public function deleteFormCategory($id)
    {
        try {

            DB::beginTransaction();

            $formCategory = FormCategory::find($id);

            $formCategory->modify_id = $this->login_user->pmaps_id;
            $formCategory->updated_at = Carbon::now();
            $formCategory->history = $formCategory->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if (!$formCategory->save()) {
                DB::rollBack();
            } else {
                if (!$formCategory->delete()) {
                    DB::rollBack();
                }
            }

            DB::commit();

            return response()->json('Form Category deleted successfully', 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }
}
