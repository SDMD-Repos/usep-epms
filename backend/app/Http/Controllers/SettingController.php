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
use App\Http\Requests\StoreMeasureRating;
use App\Http\Requests\StoreProgram;
use App\Http\Requests\StoreOtherProgram;
use App\Http\Requests\StoreSignatory;
use App\Http\Requests\StoreSubCategory;
use App\Http\Requests\UpdateDefaultProgram;
use App\Http\Requests\UpdateGroup;
use App\Http\Requests\UpdateMeasure;
use App\Http\Requests\UpdateMeasureRating;
use App\Http\Traits\ConverterTrait;
use App\Http\Traits\OfficeTrait;
use App\Measure;
use App\MeasureCategory;
use App\MeasureItem;
use App\MeasureRating;
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
    use ConverterTrait, OfficeTrait;

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

            return $next($request);
        });
    }

    public function getFunctions($year, $formId=null)
    {
        try {
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
        } catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
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
            $category->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
            $category->history = $category->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
            $category->history = $category->history . "Update Default Program " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
        try {
            $subCategories = SubCategory::with('category')->where('year', $year)
                ->orderBy('category_id', 'ASC')->orderBy('ordering', 'ASC')->get();

            $isNested = filter_var($isNested, FILTER_VALIDATE_BOOLEAN);

            $modSubCategories = $isNested ? $this->getNestedChildren($subCategories) : $subCategories;

            return response()->json([
                'subCategories' => $modSubCategories
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

    public function createSubCategory(StoreSubCategory $request)
    {
        try {

            $validated = $request->validated();

            DB::beginTransaction();

            $subcategory = new SubCategory();

            $subcategory->name = $validated['name'];
            $subcategory->category_id = $validated['category_id'];
            $subcategory->parent_id = $validated['parentId'];
            $subcategory->ordering = $validated['ordering'];
            $subcategory->year = $validated['year'];
            $subcategory->create_id = $this->login_user->pmaps_id;
            $subcategory->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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

    public function updateSubCategory(StoreSubCategory $request)
    {
        try {
            $validated = $request->validated();

            $history = '';

            DB::beginTransaction();

            $subcategory = SubCategory::find($validated['id']);

            $original = $subcategory->getOriginal();

            $childSubcategories = SubCategory::where('parent_id', $validated['id'])->get();

            foreach ($childSubcategories as $childSubcategory) {
                $childSubcategory->ordering = ($childSubcategory->ordering - (int)$childSubcategory->ordering) + $validated['ordering'];
                if (!$childSubcategory->save()) {
                    DB::rollBack();
                }
            }

            if($subcategory->isDirty('ordering')){
                $history .= "Update Ordering from '".$original['ordering']."' to '".$validated['ordering']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            $subcategory->name = $validated['name'];
            $subcategory->category_id = $validated['category_id'];
            $subcategory->parent_id = $validated['parentId'];
            $subcategory->ordering = $validated['ordering'];
            $subcategory->year = $validated['year'];
            $subcategory->updated_at = Carbon::now();
            $subcategory->modify_id = $this->login_user->pmaps_id;
            $subcategory->history = $subcategory->history . $history;

            if (!$subcategory->save()) {
                DB::rollBack();
            }

            DB::commit();

            return response()->json([
                'success' => 'Sub category updated successfully'
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
                $sub->history = $sub->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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

    public function getPrograms($year, $formId)
    {
        try {
            $programs = Program::select("*", "id as key")->where('year', $year)
                ->where(function($q) use ($formId) {
                    $q->where('form_id', null)->orWhere('form_id', $formId ?: null);
                })->with('category')->get();

            return response()->json([
                'programs' => $programs
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

    public function getOtherPrograms($year,$form_id)
    {
        try {
            $programs = OtherProgram::select("*", "id as key")->where('year', $year)->where('form_id',$form_id)->with('category')->get();

            return response()->json([
                'otherPrograms' => $programs
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
            $program->form_id = $validated['form_id'] ?? null;
            $program->create_id = $this->login_user->pmaps_id;
            $program->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
            $program->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
            $program->history = $program->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
            $program->history = $program->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
        try {
            $measures = Measure::select('*', 'id as key')->where('year', $year)
                ->with(['categories.items.rating', 'customItems.rating'])->get();

            return response()->json([
                'measures' => $measures
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

    public function createMeasure(StoreMeasure $request)
    {
        try {
            $validated = $request->validated();

            $measureList = $validated['measures'];

            $year = $validated['year'];

            DB::beginTransaction();

            foreach($measureList as $list) {
                $measure = new Measure;

                $measure->year = $year;
                $measure->name = $list['name'];
                $measure->display_as_items = $list['displayAsItems'];
                $measure->is_custom = $list['isCustom'];
                $measure->description = $list['description'];
                $measure->variable_equivalent = $list['variableEquivalent'];
                $measure->elements = $list['elements'];
                $measure->bg_color = $list['bgColor'];
                $measure->create_id = $this->login_user->pmaps_id;
                $measure->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

                if ($measure->save()) {
                    $measureId = $measure->id;

                    if(!$list['isCustom']) {
                        $this->createMeasureCategory([
                            'categories' => $list['categories'],
                            'measureId' => $measureId,
                            'year' => $year,
                        ]);

                    }else {
                        $this->createMeasureItems([
                            'measureId' => $measureId,
                            'categoryId' => NULL,
                            'items' => $list['customItems'],
                            'year' => $year,
                        ]);
                    }
                } else {
                    DB::rollBack();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Measure created successfully'
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

    /**
     * @param array $data [ categories, measureId ]
     * @return void
     */

    protected function createMeasureCategory(array $data)
    {
        extract($data);

        foreach ($categories as $category) {
            $measureCategory = new MeasureCategory();

            $measureCategory->measure_id = $measureId;
            $measureCategory->numbering = $category['numbering'];
            $measureCategory->name = $category['name'];
            $measureCategory->create_id = $this->login_user->pmaps_id;
            $measureCategory->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

            if (!$measureCategory->save()) {
                DB::rollBack();
            }else {
                $this->createMeasureItems([
                    'measureId' => $measureId,
                    'categoryId' => $measureCategory->id,
                    'items' => $category['items'],
                    'year' => $year
                ]);
            }
        }
    }

    /**
     * @param array $data [ items, measureId, categoryId ]
     * @return void
     */

    protected function createMeasureItems(array $data)
    {
        extract($data);

        foreach($items as $item) {
            $ratingId = $item['rating']['value'] ?? ($item['rating']['id'] ?? $item['rating']);
            $numerical_rating = $item['rating']['label'] ?? ($item['rating']['numerical_rating'] ?? null);

            if(!$numerical_rating) {
                $numerical_rating = MeasureRating::where('id', $ratingId)->first();
            }

            $rating = MeasureRating::where([
                ['numerical_rating', $numerical_rating],
                ['year', 2023],
            ])->first();

            if(!$rating) {
                $prevRating = MeasureRating::find($ratingId);
                $prevRating->year = $year;

                $new = []; $new[] = $prevRating;

                $ratingId = $this->createMeasureRatings($new, 1);
            }else {
                $ratingId = $rating->id;
            }

            $newItem = new MeasureItem;

            $newItem->measure_id = $measureId;
            $newItem->category_id = $categoryId;
            $newItem->rating = $ratingId;
            $newItem->description = $item['description'];
            $newItem->create_id = $this->login_user->pmaps_id;
            $newItem->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

            $newItem->save();
        }
    }

    /**
     * @param StoreMeasure $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function updateMeasure(UpdateMeasure $request, $id)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $measure = Measure::find($id);

            $original = $measure->getOriginal();

            $measure->name = $validated['name'];
            $measure->display_as_items = $validated['displayAsItems'];
            $measure->is_custom = $validated['isCustom'];
            $measure->description = $validated['description'];
            $measure->variable_equivalent = $validated['variableEquivalent'];
            $measure->elements = $validated['elements'];
            $measure->bg_color = $validated['bgColor'];

            $history = '';

            if($measure->isDirty('name')){
                $history .= "Updated Name from '".$original['name']."' to '".$validated['name']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($measure->isDirty('display_as_items')){
                $history .= "Updated display_as_items from '".$original['display_as_items']."' to '".$validated['displayAsItems']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($measure->isDirty('is_custom')){
                $history .= "Updated is_custom from '".$original['is_custom']."' to '".$validated['isCustom']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($measure->isDirty('description')){
                $history .= "Updated Description from '".$original['description']."' to '".$validated['description']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($measure->isDirty('variable_equivalent')){
                $history .= "Updated Variable Equivalent from '".$original['variable_equivalent']."' to '".$validated['variableEquivalent']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($measure->isDirty('elements')){
                $history .= "Updated Elements from '".$original['elements']."' to '".$validated['elements']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            $measure->modify_id = $this->login_user->pmaps_id;
            $measure->updated_at = Carbon::now();
            $measure->history = $measure->history . $history;

            if ($measure->save()) {
                $this->processMeasures($original, $validated);
            } else {
                DB::rollBack();
            }

            DB:: commit();

            return response()->json([
                'message' => 'Measure updated successfully'
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

    protected function processMeasures($original, $validated)
    {
        $measureId = $validated['id'];
        $isCustom = $validated['isCustom'];
        $categories = $validated['categories'];
        $customItems = $validated['customItems'];
        $year = $validated['year'];

        if((boolean)$original['is_custom'] === $isCustom) {
            if(!$isCustom) {
                foreach($categories as $category) {
                    if(!isset($category['status']) || $category['status'] !== 'new') {
                        $findCategory = MeasureCategory::find($category['id']);

                        $orig = $findCategory->getOriginal();

                        $findCategory->numbering = $category['numbering'];
                        $findCategory->name = $category['name'];

                        $history = '';

                        if($findCategory->isDirty('numbering')) {
                            $history .= "Updated Numbering from '".$orig['numbering']."' to '".$category['numbering']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
                        }

                        if($findCategory->isDirty('name')) {
                            $history .= "Updated Name from '".$orig['name']."' to '".$category['name']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
                        }

                        $findCategory->modify_id = $this->login_user->pmaps_id;
                        $findCategory->updated_at = Carbon::now();
                        $findCategory->history = $findCategory->history . $history;

                        if($findCategory->save()) {
                            $this->updateMeasureItems([
                                'items' => $category['items'],
                                'measureId' => $category['measure_id'],
                                'categoryId' => $category['id'],
                                'year' => $year
                            ]);
                        }
                    }else {
                        $this->createMeasureCategory([
                            'categories' => [$category],
                            'measureId' => $measureId,
                            'year' => $year
                        ]);
                    }
                }
            }else {
                $this->updateMeasureItems([
                    'items' => $customItems,
                    'measureId' => $measureId,
                    'categoryId' => null,
                    'year' => $year
                ]);
            }

            $deleted = $validated['deleted'];

            if (!empty($deletedCategories = $deleted['categories'])) {
                foreach ($deletedCategories as $id) {
                    $measureCategory = MeasureCategory::find($id);

                    $measureCategory->modify_id = $this->login_user->pmaps_id;
                    $measureCategory->updated_at = Carbon::now();
                    $measureCategory->history = $measureCategory->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

                    if ($measureCategory->save()) {
                        $measureCategory->delete();
                    }
                }
            }

            if (!empty($deletedItems = $deleted['items'])) {
                foreach ($deletedItems as $id) {
                    $measureItems = MeasureItem::find($id);

                    $measureItems->modify_id = $this->login_user->pmaps_id;
                    $measureItems->updated_at = Carbon::now();
                    $measureItems->history = $measureItems->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

                    if ($measureItems->save()) {
                        $measureItems->delete();
                    }
                }
            }
        }else {
            MeasureItem::where('measure_id', $measureId)->delete();

            if(!$isCustom) {
                $this->createMeasureCategory([
                    'categories' => $categories,
                    'measureId' => $measureId,
                    'year' => $year,
                ]);
            }else {
                MeasureCategory::where('measure_id', $measureId)->delete();

                $this->createMeasureItems([
                    'items' => $customItems,
                    'measureId' => $measureId,
                    'categoryId' => null,
                    'year' => $year,
                ]);
            }
        }
    }

    /**
     * @param array $data [ items, measureId, categoryId ]
     * @return void
     */

    protected function updateMeasureItems(array $data)
    {
        extract($data);

        foreach($items as $item) {
            if(!isset($item['status']) || $item['status'] !== 'new') {
                $findItem = MeasureItem::find($item['id']);

                $orig = $findItem->getOriginal();

                $rating = $item['rating']['value'] ?? ($item['rating']['id'] ?? $item['rating']);

                $findItem->rating = $rating;
                $findItem->description = $item['description'];

                $history = '';

                if($findItem->isDirty('rating')) {
                    $history .= "Updated Rating from '".$orig['rating']."' to '".$rating."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
                }

                if($findItem->isDirty('description')) {
                    $history .= "Updated Description from '".$orig['description']."' to '".$item['description']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
                }

                $findItem->modify_id = $this->login_user->pmaps_id;
                $findItem->updated_at = Carbon::now();
                $findItem->history = $findItem->history . $history;

                $findItem->save();
            }else {
                $this->createMeasureItems([
                    'items' => [$item],
                    'measureId' => $measureId,
                    'categoryId' => $categoryId,
                    'year' => $year
                ]);
            }
        }
    }

    public function deleteMeasure($id)
    {
        try {

            DB::beginTransaction();

            $measure = Measure::find($id);

            $measure->modify_id = $this->login_user->pmaps_id;
            $measure->updated_at = Carbon::now();
            $measure->history = $measure->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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

    public function getMeasureRatings($year)
    {
        try {
            $measureRatings = MeasureRating::select("*", "id as key")->where('year', $year)->get();

            return response()->json([
                'measureRatings' => $measureRatings
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

    public function validateMeasureRating(StoreMeasureRating $request)
    {
        try {
            $validated = $request->validated();
            $ratings = $validated['ratings'];

            DB::beginTransaction();

            $this->createMeasureRatings($ratings);

            DB::commit();

            return response()->json([
                'message' => 'Rating created successfully'
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

    public function createMeasureRatings($ratings, $fromPrevious=0)
    {
        foreach($ratings as $rating) {
            $measureRating = new MeasureRating();

            $measureRating->year = $rating['year'];
            $measureRating->numerical_rating = $rating['numerical_rating'];
            $measureRating->aps_from = $rating['aps_from'];
            $measureRating->aps_to = $rating['aps_to'];
            $measureRating->adjectival_rating = $rating['adjectival_rating'];
            $measureRating->description = $rating['description'];
            $measureRating->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

            if(!$measureRating->save()) {
                DB::rollBack();
            }

            if($fromPrevious) { return $measureRating->id; }
        }
    }

    public function updateMeasureRating(UpdateMeasureRating $request, $id)
    {
        try {
            $validated = $request->validated();

            $numericalRating = $validated['numerical_rating'];
            $aps_from  = $validated['aps_from'];
            $aps_to  = $validated['aps_to'];
            $adjectivalRating  = $validated['adjectival_rating'];
            $description  = $validated['description'];

            DB::beginTransaction();

            $measureRating = MeasureRating::find($id);

            $original = $measureRating->getOriginal();

            $measureRating->numerical_rating = $numericalRating;
            $measureRating->aps_from = $aps_from;
            $measureRating->aps_to = $aps_to;
            $measureRating->adjectival_rating = $adjectivalRating;
            $measureRating->description = $description;

            $history = '';

            if($measureRating->isDirty('numerical_rating')){
                $history .= "Updated Numerical Rating from ".$original['numerical_rating']." to ".$numericalRating." ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($measureRating->isDirty('aps_from')){
                $history .= "Updated APS From from ".$original['aps_from']." to ".$aps_from." ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($measureRating->isDirty('aps_to')){
                $history .= "Updated APS To from ".$original['aps_to']." to ".$aps_to." ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($measureRating->isDirty('adjectival_rating')){
                $history .= "Updated Adjectival Rating from '".$original['adjectival_rating']."' to '".$adjectivalRating."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($measureRating->isDirty('description')){
                $history .= "Updated Description from '".$original['description']."' to '".$description."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            $measureRating->history = $measureRating->history . $history;

            if (!$measureRating->save()) {
                DB::rollBack();
            }

            DB:: commit();

            return response()->json([
                'message' => 'Rating updated successfully'
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

    public function deleteMeasureRating($id)
    {
        try {

            DB::beginTransaction();

            $mesureRating = MeasureRating::find($id);

            if(count($mesureRating->items)) {
                $message = 'Unable to delete rating';
                $description = 'There were already saved measure items under Rating ' . $mesureRating->numerical_rating;
            }else {
                $mesureRating->modify_id = $this->login_user->pmaps_id;
                $mesureRating->updated_at = Carbon::now();
                $mesureRating->history = $mesureRating->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

                if (!$mesureRating->save()) {
                    DB::rollBack();
                } else {
                    if (!$mesureRating->delete()) {
                        DB::rollBack();
                    }
                }

                DB::commit();

                $message = 'Success';
                $description = 'Rating deleted successfully';
            }

            return response()->json([
                'type' => $message === 'Success' ? 'success' : 'error',
                'message' => $message, 'description' => $description
            ], 202);
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
        try {
            $forms = Form::orderBy('ordering', 'asc')->get();

            return response()->json(['forms' => $forms], 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function getUserFormAccess()
    {
        try {
            $pmaps_id = $this->login_user->pmaps_id;

            $userForms = FormAccess::where(function($q) use ($pmaps_id) {
                $q->where('pmaps_id', $pmaps_id)->orWhere('staff_id', $pmaps_id);
            })->with('form')->get();

            return response()->json([
                'userForms' => $userForms
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

    public function getAllSignatoryTypes()
    {
        try {
            $signatoryTypes = SignatoryType::orderBy('ordering', 'ASC')->get();

            return response()->json([
                'signatoryTypes' => $signatoryTypes
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

    public function getYearSignatories($year, $formId, $officeId)
    {
        try {

            if(!$officeId) {
                $signatories = Signatory::select("*", "id as key")->where([
                    ['form_id', $formId],
                    ['year', $year]
                ])->get();
            } else {
                $condition = [
                    ['form_id', $formId],
                    ['year', $year],
                    ['office_form_id', $officeId]
                ];
                $signatories = Signatory::select("*", "id as key")->where($condition)->get();
            }

//            dd($signatories);

            foreach($signatories as $i => $signatory) {
                $offices = $this->getPersonnelByOffice($signatory->office_id, 1, $signatory->is_subunit, 0, 0);

                if($signatory['is_subunit']) {
                    $signatories[$i]['office_id'] = "SUB".$signatory['office_id'];
                }

                $signatories[$i]['memberList'] = $offices;
            }

            return response()->json([
                'signatories' => $signatories
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

                $isSubunit = $signatory['isSubunit'] ?? 0;

                if(!$signatory['isCustom']) {
                    $officeName = $signatory['officeId']['label'];
                    $officeId = $signatory['officeId']['value'];

                    if($isSubunit) {
                        $officeId = str_replace("SUB", "", $officeId);
                    }

                    $personnelName = $signatory['personnelId']['label'];
                    $personnelId = $signatory['personnelId']['value'];
                } else {
                    $officeId = null;
                    $officeName = $signatory['officeId'];

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
                $newSignatory->is_subunit = $isSubunit;
                $newSignatory->office_name = $officeName;
                $newSignatory->position = $signatory['position'];
                $newSignatory->office_form_id = $officeFormId; // selected office for the signatories added
                $newSignatory->create_id = $this->login_user->pmaps_id;
                $newSignatory->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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

                $isSubunit = $signatory['isSubunit'] ?? 0;

                if(!$signatory['isCustom']) {
                    $officeName = $signatory['officeId']['label'];
                    $officeId = $signatory['officeId']['value'];

                    if($isSubunit) {
                        $officeId = str_replace("SUB", "", $officeId);
                    }

                    $personnelName = $signatory['personnelId']['label'];
                    $personnelId = $signatory['personnelId']['value'];
                } else {
                    $officeId = null;
                    $officeName = $signatory['officeId'];

                    $personnelId = null;
                    $personnelName = $signatory['personnelId'];
                }

                if($check) {
                    $original = $check->getOriginal();

                    if ($check->office_id !== $officeId || ($signatory['isCustom'] && $check->office_name !== $officeName)) {
                        $check->office_id = $officeId;
                        $check->office_name = $officeName;
                        $history .= "Updated office from '" . $original['office_name'] . "' to '" . $officeName . "' " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                    }

                    if($check->personnel_id !== $personnelId || ($signatory['isCustom'] && $check->personnel_name !== $personnelName)) {
                        $check->personnel_id = $personnelId;
                        $check->personnel_name = $personnelName;
                        $history .= "Updated personnel from '" . $original['personnel_name'] . "' to '" . $personnelName . "' " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                    }

                    if($check->position !== $signatory['position']) {
                        $check->position = $signatory['position'];
                        $history .= "Updated position from '" . $original['position'] . "' to '" . $signatory['position'] . "' " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
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
                    $newSignatory->is_subunit = $isSubunit;
                    $newSignatory->office_name = $officeName;
                    $newSignatory->position = $signatory['position'];
                    $newSignatory->create_id = $this->login_user->pmaps_id;
                    $newSignatory->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
            $signatory->history = $signatory->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
        try {
            $groups = Group::select('*', 'id as key')->with('members')->get();

            return response()->json([
                'groups' => $groups
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
                $chairId = $validated['chair']['id'];
                $chairOffice = $validated['chair']['office'];
                $isSubunit = $validated['chair']['isSubunit'] ?? 0;

                if($isSubunit)
                    $chairOffice['value'] = str_replace("SUB", "", $chairOffice['value']);

                $group->oic_id = $chairId['value'];
                $group->oic_name = trim($chairId['label']);
                $group->is_subunit = $isSubunit;
                $group->oic_dept_id = trim($chairOffice['value']);
                $group->oic_dept_name = trim($chairOffice['label']);
            }

            $group->create_id = $this->login_user->pmaps_id;
            $group->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
            $isSubunit = $member['isSubunit'] ?? 0;

            if($isSubunit)
                $office['value'] = str_replace("SUB", "", $office['value']);

            $newMember = new GroupMember;

            $newMember->member_id = $detail['value'];
            $newMember->member_name = trim($detail['label']);
            $newMember->is_subunit =  $isSubunit;
            $newMember->office_id = $office['value'];
            $newMember->office_name = $office['label'];
            $newMember->create_id = $this->login_user->pmaps_id;
            $newMember->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
                $chairId = $validated['chair']['id'];
                $chairOffice = $validated['chair']['office'];
                $isSubunit = $validated['chair']['isSubunit'] ?? 0;

                if($isSubunit)
                    $chairOffice['value'] = str_replace("SUB", "", $chairOffice['value']);

                $group->oic_id = $chairId['value'];
                $group->oic_name = trim($chairId['label']);
                $group->oic_dept_id = trim($chairOffice['value']);
                $group->is_subunit = $isSubunit;
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
                $history .= "Updated Name from '".$original['name']."' to '".$name."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($group->isDirty('effective_until')){
                $history .= "Updated Effective Until from '".$original['effective_until']."' to '".$effectivity."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($group->isDirty('supervising_id')){
                $history .= "Updated Supervising office from '".$original['supervising_name']."' to '".$supervising['label']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($group->isDirty('oic_id')){
                $history .= "Updated Officer-in-Charge from '".$original['oic_name']."' to '".$group->oic_name."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
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
                    $deleteMember->history = $deleteMember->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
            $group->history = $group->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
        try {
            $cascadingLevels = CascadingLevel::orderBy('ordering', 'ASC')->get();

            return response()->json([
                'cascadingLevels' => $cascadingLevels
            ], 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode() && ($e->getCode() < 511)) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function getAllFormFields($year, $formId)
    {
        try {
            $formFields = FormField::with(['settings' => function($query) use ($year, $formId) {
                $query->where('year', $year)->where('form_id', $formId);
            }])->get();

            return response()->json([
                'formFields' => $formFields
            ], 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode() && ($e->getCode() < 511)) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
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
            $settings->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
                $history .= "Updated Setting from '".$original['setting']."' to '".$setting['value']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
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
                    $history .= "Updated Display Name from '".$original['display_name']."' to '".$displayName."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
                }

                $formCategory->history = $formCategory->history . $history;

                $successMessage = 'Form Category was updated successfully';

            } else {
                $formCategory = new FormCategory();

                $formCategory->form_id = $formId;
                $formCategory->category_id = $categoryId;
                $formCategory->display_name = $displayName;
                $formCategory->create_id = $this->login_user->pmaps_id;
                $formCategory->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
            $formCategory->history = $formCategory->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
