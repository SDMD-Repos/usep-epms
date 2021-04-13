<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Requests\StoreMeasure;
use App\Http\Requests\StoreProgram;
use App\Http\Requests\StoreSubCategory;
use App\Http\Traits\ConverterTrait;
use App\Measure;
use App\MeasureItem;
use App\Program;
use App\SubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    use ConverterTrait;

    private $login_user;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth:api');

        $this->middleware(function ($request, $next) {
            $this->login_user = Auth::user();

            return $next($request);
        });

    }

    public function getFunctions()
    {
        $categories = Category::all();

        foreach ($categories as $key => $category) {
            $categories[$key]['header'] = $this->integerToRomanNumeral($category->order) . ". " . mb_strtoupper($category->name);
        }

        return response()->json([
            'categories' => $categories
        ], 200);
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
            $subcategory->history = "Created " . Carbon::now() . " by " . $this->login_user->pmaps_id . "\n";

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

            return response()->json([
                'error' => $e->getMessage()
            ], $status);
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
                $sub->history = $sub->history . "Deleted " . Carbon::now() . " by " . $this->login_user->pmaps_id . "\n";

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
            $program->history = "Created " . Carbon::now() . " by " . $this->login_user->pmaps_id . "\n";

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
            $program->history = $program->history . "Deleted " . Carbon::now() . " by " . $this->login_user->pmaps_id . "\n";

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
            $measure->history = "Created " . Carbon::now() . " by " . $this->login_user->pmaps_id . "\n";

            if ($measure->save()) {
                foreach ($validated['items'] as $item) {
                    $measureItem = new MeasureItem();

                    $measureItem->measure_id = $measure->id;
                    $measureItem->rate = (int)$item['rate'];
                    $measureItem->description = $item['description'];
                    $measureItem->create_id = $this->login_user->pmaps_id;
                    $measureItem->history = "Created " . Carbon::now() . " by " . $this->login_user->pmaps_id . "\n";

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

            $measure->history = $measure->history . "Updated " . Carbon::now() . $changes . " by " . $this->login_user->pmaps_id . "\n";
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
                        $measureItem->history = "Created " . Carbon::now() . " by " . $this->login_user->pmaps_id . "\n";

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
                    $measureItem->history = $measureItem->history . "Deleted " . Carbon::now() . " by " . $this->login_user->pmaps_id . "\n";

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
            $measure->history = $measure->history . "Deleted " . Carbon::now() . " by " . $this->login_user->pmaps_id . "\n";

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
}
