<?php

namespace App\Http\Controllers\Form;

use App\Models\Aapcr;
use App\Models\AapcrDetail;
use App\Models\AapcrDetailMeasure;
use App\Models\AapcrDetailOffice;
use App\Models\AapcrProgramBudget;
use App\Models\FormField;
use App\Models\FormUnpublishStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAapcr;
use App\Http\Requests\UnpublishForm;
use App\Http\Requests\UpdateAapcr;
use App\Http\Traits\FileTrait;
use App\Http\Traits\FormTrait;
use App\Http\Traits\OfficeTrait;
use App\Http\Traits\PdfTrait;
use App\Models\Program;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;


class AapcrController extends Controller
{
    use PdfTrait, OfficeTrait, FileTrait, FormTrait;

    public function checkSaved($year)
    {
        try {
            $getSaved = Aapcr::where([
                ['year', $year],
                ['is_active', 1],
            ])->orderBy('created_at', 'DESC')->first();

            return response()->json([
                'hasSaved' => isset($getSaved->id)
            ], 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function print_aapcr($id)
    {
        // Generate a PDF or get the Dompdf instance

        $aapcr = $this->viewAapcrPdf($id, 1);
        // return $aapcr;
        $pdf = PDF::loadView('print.aapcr', compact('aapcr'))->setPaper("legal", "landscape");
        // Get the Dompdf options instance
        $options = $pdf->getDomPDF()->getOptions();

        // Add multiple options
        $options->set('margin-top', '0');
        $options->set('margin-right', '0');
        $options->set('margin-bottom', '0');
        $options->set('margin-left', '0');
        $options->set('dpi', '150');

        // Set the updated options
        $pdf->getDomPDF()->setOptions($options);
        return $pdf->stream('aapcr.pdf');
    }


    public function print_vpopcr($id)
    {
        // Generate a PDF or get the Dompdf instance

        $vpopcr = $this->viewVpOpcrPdf($id, 1);
        // return $aapcr;
        $pdf = PDF::loadView('print.vpopcr', compact('vpopcr'))->setPaper("legal", "landscape");
        // Get the Dompdf options instance
        $options = $pdf->getDomPDF()->getOptions();

        // Add multiple options
        $options->set('margin-top', '0');
        $options->set('margin-right', '0');
        $options->set('margin-bottom', '0');
        $options->set('margin-left', '0');
        $options->set('dpi', '150');

        // Set the updated options
        $pdf->getDomPDF()->setOptions($options);
        return $pdf->stream('vpopcr.pdf');
    }


    public function getAllAapcrs()
    {
        try {
            $aapcrList = Aapcr::select("*", "id as key")->with('status')->orderBy('created_at', 'ASC')->get();

            return response()->json([
                'aapcrList' => $aapcrList
            ], 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function save(StoreAapcr $request)
    {
        try {
            $validated = $request->validated();

            $dataSource = $validated['dataSource'];
            $year = $validated['fiscalYear'];
            $documentName = $validated['documentName'];
            $isFinalized = $validated['isFinalized'];
            $programBudgets = $validated['programBudgets'];

            $finalizedHistory = ($isFinalized ? 'and finalized ' : '');

            $hasSavedAapcr = Aapcr::where([
                ['year', $year],
                ['is_active', 1]
            ])->get();

            if ($hasSavedAapcr->count() > 0) {
                return response()->json('Unable to save this document. A finalized AAPCR has been created for the year ' . $year . '.', 409);
            }

            DB::beginTransaction();

            $aapcr = new Aapcr();

            $aapcr->year = $year;
            $aapcr->document_name = $documentName;
            $aapcr->finalized_date = ($isFinalized ? Carbon::now() : null);
            $aapcr->create_id = $this->login_user->pmaps_id;
            $aapcr->history = "Created " . $finalizedHistory . Carbon::now() . " by " . $this->login_user->fullname . "\n";

            if ($aapcr->save()) {
                foreach ($dataSource as $value) {
                    $this->saveAapcrDetails($aapcr->id, $value);
                }

                $this->saveProgramBudgets($aapcr->id, $programBudgets);
            } else {
                DB::rollBack();
            }

            DB::commit();

            return response()->json('AAPCR was saved successfully', 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function saveAapcrDetails($aapcrId, $values)
    {
        $formFields = FormField::select('id', 'code')->whereIn('code', ['implementing', 'supporting'])->get();

        $detail = new AapcrDetail();

        if (isset($values['subCategory'])) {
            $category_id = SubCategory::find($values['subCategory']['value']);
        } else {
            $category_id = Program::find($values['program']);
        }

        $detail->aapcr_id = $aapcrId;
        $detail->category_id = $category_id->category->id;
        $detail->sub_category_id = isset($values['subCategory']) ? $values['subCategory']['value'] : null;
        $detail->program_id = $values['program'];
        $detail->pi_name = $values['name'];
        $detail->is_header = $values['isHeader'];

        if (!$values['isHeader']) {
            $detail->target = $values['target'];
            $detail->allocated_budget = $values['budget'];
            $detail->targets_basis = $values['targetsBasis'];
            $detail->cascading_level = $values['cascadingLevel']['key'];
            $detail->other_remarks = $values['remarks'];
        }

        $detail->parent_id = $values['detailId'] ?? null;
        $detail->linked_to_child = $values['linkedToChild'] ?? 0;
        $detail->create_id = $this->login_user->pmaps_id;
        $detail->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

        if ($detail->save()) {
            $this->saveMeasures($detail, $values['measures']);

            $officeModel = new AapcrDetailOffice();

            foreach ($formFields as $formField) {
                $this->saveOffices([
                    'form' => 'aapcr',
                    'model' => $officeModel,
                    'detailId' => $detail->id,
                    'offices' => $values[$formField->code],
                    'fieldName' => $formField->id
                ]);
            }

            if (isset($values['children']) && count((array)$values['children'])) {

                foreach ($values['children'] as $child) {
                    $child['detailId'] = $detail->id;

                    $this->saveAapcrDetails($aapcrId, $child);
                }
            }
        }
    }

    public function saveProgramBudgets($aapcrId, $budgets)
    {
        foreach ($budgets as $budget) {
            $newBudget = new AapcrProgramBudget();

            $newBudget->aapcr_id = $aapcrId;
            $newBudget->program_id = $budget['mainCategory']['key'];
            $newBudget->budget = $budget['categoryBudget'];
            $newBudget->create_id = $this->login_user->pmaps_id;
            $newBudget->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

            if (!$newBudget->save()) {
                DB::rollBack();
            }
        }
    }

    public function publish(Request $request)
    {
        try {
            $id = $request->id;
            $year = $request->year;

            $hasPublished = Aapcr::where([
                ['year', $year],
                ['is_active', 1]
            ])->whereNotNull('published_date')->first();

            if (!$hasPublished) {
                $aapcr = Aapcr::find($id);

                $aapcr->published_date = Carbon::now();
                $aapcr->updated_at = Carbon::now();
                $aapcr->modify_id = $this->login_user->pmaps_id;
                $aapcr->history = $aapcr->history . "Published " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

                $filename = $this->viewAapcrPdf($id, 1);

                $aapcr->published_file = $filename;

                $aapcr->save();

                return response()->json('AAPCR was published successfully', 200);
            } else {
                return response()->json('Cannot publish two or more AAPCRs in a year', 400);
            }
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function unpublish(UnpublishForm $request)
    {
        try {
            $validated = $request->validated();

            $remarks = $validated['remarks'];
            $id = $validated['id'];
            $document_name = $validated['documentName'];
            $fileName = $validated['fileName'];

            DB::beginTransaction();

            $unpublished = new FormUnpublishStatus();

            $unpublished->form_type = 'aapcr';
            $unpublished->form_id = $id;
            $unpublished->document_name = $document_name;
            $unpublished->remarks = $remarks;
            $unpublished->status = 'pending';
            $unpublished->requested_date = Carbon::now();
            $unpublished->requested_by = $this->login_user->fullname;
            $unpublished->file_name = $fileName;
            $unpublished->create_id = $this->login_user->pmaps_id;
            $unpublished->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

            if (!$unpublished->save()) {
                DB::rollBack();
            }

            DB::commit();

            return response()->json('AAPCR was unpublished successfully', 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function deactivate(Request $request)
    {
        try {
            $id = $request->id;

            $now = Carbon::now();

            $aapcr = Aapcr::find($id);

            $aapcr->is_active = 0;
            $aapcr->end_effectivity = $now;
            $aapcr->updated_at = $now;
            $aapcr->modify_id = $this->login_user->pmaps_id;
            $aapcr->history = $aapcr->history . "Deactivated " . $now . " by " . $this->login_user->fullname . "\n";

            $aapcr->save();

            return response()->json("Successfully deactivated", 200);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function view($id)
    {
        $aapcr = Aapcr::with('detailParents.subDetails')->find($id);

        # Fetch program's budget
        $programBudgets = AapcrProgramBudget::where('aapcr_id', $id)->orderBy('program_id', 'ASC')->get();

        $programsSummary = [];

        foreach ($programBudgets as $programBudget) {
            $mainCategory = new \stdClass();

            $mainCategory->key = $programBudget->program_id;
            $mainCategory->label = $programBudget->program->name;

            $budget = new \stdClass();

            $budget->categoryBudget = $programBudget->budget;
            $budget->mainCategory = $mainCategory;

            $programsSummary[] = $budget;
        }
        # end

        $dataSource = [];

        $this->targetsBasisList = []; // Reset $this->targetsBasisList from ConverterTrait

        # Loop through AAPCR details
        foreach ($aapcr->detailParents as $detail) {
            $offices = $this->splitPIOffices($detail->offices, ['origin' => 'aapcr']);

            $implementing = count((array)$offices) ? $offices['implementing'] : [];

            $supporting = $offices['supporting'] ?? [];

            $subs = [];

            if (count((array)$detail->subDetails)) {
                foreach ($detail->subDetails as $subPI) {

                    $subPIOffices = $this->splitPIOffices($subPI->offices, ['origin' => 'aapcr']);

                    $subImplementing = $subPIOffices['implementing'];

                    $subSupporting = $subPIOffices['supporting'] ?? [];

                    $this->getTargetsBasisList($subPI->targets_basis);

                    $extracted = $this->extractDetails($subPI);

                    $subItem = array(
                        'key' => $subPI->id,
                        'id' => $subPI->id,
                        'type' => 'sub',
                        'subCategory' => $extracted['subCategory'],
                        'program' => $subPI->program_id,
                        'name' => $subPI->pi_name,
                        'isHeader' => (bool)$subPI->is_header,
                        'target' => $subPI->target,
                        'measures' => $extracted['measures'],
                        'budget' => $subPI->allocated_budget,
                        'targetsBasis' => $subPI->targets_basis,
                        'cascadingLevel' => $extracted['cascadingLevel'],
                        'implementing' => $subImplementing,
                        'supporting' => $subSupporting,
                        'remarks' => $subPI->other_remarks,
                        'parentId' => $subPI->parent_id,
                    );

                    $subs[] = $subItem;
                }
            }

            $this->getTargetsBasisList($detail->targets_basis);

            $extracted = $this->extractDetails($detail);

            if (!$detail->parent_id) {
                $item = array(
                    'key' => $detail->id,
                    'id' => $detail->id,
                    'type' => 'pi',
                    'subCategory' => $extracted['subCategory'],
                    'program' => $detail->program_id,
                    'name' => $detail->pi_name,
                    'isHeader' => (bool)$detail->is_header,
                    'target' => $detail->target,
                    'measures' => $extracted['measures'],
                    'budget' => $detail->allocated_budget,
                    'targetsBasis' => $detail->targets_basis,
                    'cascadingLevel' => $extracted['cascadingLevel'],
                    'implementing' => $implementing,
                    'supporting' => $supporting,
                    'remarks' => $detail->other_remarks,
                    'linkedToChild' => $detail->linked_to_child,
                    'parent_id' => null,
                );

                if (count((array)$subs)) {
                    $item['children'] = $subs;
                }

                $dataSource[] = $item;
            }
        }
        # end AAPCR details loop

        return response()->json([
            'dataSource' => $dataSource,
            'year' => $aapcr->year,
            'isFinalized' => $aapcr->finalized_date !== NULL,
            'targetsBasisList' => $this->targetsBasisList,
            'budgetList' => $programsSummary,
            'editMode' => true
        ], 200);
    }

    public function update(UpdateAapcr $request, $id)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $dataSource = $validated['dataSource'];
            $year = $validated['fiscalYear'];
            $isFinalized = $validated['isFinalized'];
            $documentName = $validated['documentName'];
            $programBudgets = $validated['programBudgets'];
            $deletedIds = $validated['deletedIds'];

            $aapcr = Aapcr::find($id);

            $history = "";

            if ($isFinalized) {
                if ($aapcr->finalized_date !== NULL) {
                    $finalized_date = $aapcr->finalized_date;
                } else {
                    $finalized_date = Carbon::now();
                    $history = 'Finalized ' . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }
            }

            $original = $aapcr->getOriginal();

            $aapcr->year = $year;
            $aapcr->document_name = $documentName;
            $aapcr->finalized_date = $isFinalized ? $finalized_date : null;
            $aapcr->modify_id = $this->login_user->pmaps_id;

            if ($aapcr->isDirty('year')) {
                $history .= "Updated year from " . $original['year'] . " to " . $year . " " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
            }

            if ($aapcr->isDirty('document_name')) {
                $history .= "Updated document name from " . $original['document_name'] . " to " . $documentName . " " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
            }

            $aapcr->history = $aapcr->history . $history;

            if ($aapcr->save()) {
                foreach ($deletedIds as $deletedId) {
                    $deleteAapcr = AapcrDetail::find($deletedId);

                    $deleteAapcr->modify_id = $this->login_user->pmaps_id;
                    $deleteAapcr->history = $deleteAapcr->history . "Deleted " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

                    if ($deleteAapcr->save()) {
                        if (!$deleteAapcr->delete()) {
                            DB::rollBack();
                        }
                    } else {
                        DB::rollBack();
                    }
                }

                foreach ($dataSource as $source) {
                    $this->updateDetails($source, $id);
                }

                $this->updateProgramBudgets($id, $programBudgets);

                DB::commit();

                return response()->json('AAPCR was updated successfully', 200);
            } else {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function updateDetails($data, $aapcrId)
    {
        if (strpos((string)$data['id'], 'new') === false) {
            $formFields = FormField::select('id', 'code')->whereIn('code', ['implementing', 'supporting'])->get();

            $detail = AapcrDetail::find($data['id']);

            if ($detail) {
                if (isset($data['subCategory'])) {
                    $category_id = SubCategory::find($data['subCategory']['value']);
                } else {
                    $category_id = Program::find($data['program']);
                }

                $original = $detail->getOriginal();

                $isHeader = $data['isHeader'] ? 1 : 0;

                $subCategory = isset($data['subCategory']) ? $data['subCategory']['value'] : null;

                $cascadingLevel = $data['cascadingLevel'] ? $data['cascadingLevel']['key'] : null;

                $detail->category_id = $category_id->category->id;
                $detail->sub_category_id = $subCategory;
                $detail->program_id = $data['program'];
                $detail->pi_name = $data['name'];
                $detail->is_header = $isHeader;
                $detail->target = $data['target'];
                $detail->allocated_budget = $data['budget'];
                $detail->targets_basis = $data['targetsBasis'];
                $detail->cascading_level = $cascadingLevel;
                $detail->other_remarks = $data['remarks'];
                $detail->linked_to_child = $data['linkedToChild'] ?? 0;
                $detail->modify_id = $this->login_user->pmaps_id;

                $history = '';

                if ($detail->isDirty('pi_name')) {
                    $history .= "Updated Performance Indicator from '" . $original['pi_name'] . "' to '" . $data['name'] . "' " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                if ($detail->isDirty('is_header')) {
                    $history .= "Updated is_header from " . $original['is_header'] . " to " . $isHeader . " " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                if ($detail->isDirty('target')) {
                    $history .= "Updated Target from '" . $original['target'] . "' to '" . $data['target'] . "' " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                if ($detail->isDirty('allocated_budget')) {
                    $history .= "Updated Allocated Budget from '" . $original['allocated_budget'] . "' to '" . $data['budget'] . "' " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                if ($detail->isDirty('targets_basis')) {
                    $history .= "Updated Targets Basis from '" . $original['targets_basis'] . "' to '" . $data['targetsBasis'] . "' " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                if ($detail->isDirty('cascading_level')) {
                    $history .= "Updated Cascading Level from '" . $original['cascading_level'] . "' to '" . $cascadingLevel . "' " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                if ($detail->isDirty('category_id')) {
                    $history .= "Updated Category ID from " . $original['category_id'] . " to " . $category_id->category->id . " " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                if ($detail->isDirty('sub_category_id')) {
                    $history .= "Updated Sub Category ID from " . $original['sub_category_id'] . " to " . $subCategory . " " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                if ($detail->isDirty('program_id')) {
                    $history .= "Updated Program ID from " . $original['program_id'] . " to " . $data['program'] . " " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                if ($detail->isDirty('other_remarks')) {
                    $history .= "Updated Other Remarks from '" . $original['other_remarks'] . "' to '" . $data['remarks'] . "' " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                $detail->history = $detail->history . $history;

                if (!$detail->save()) {
                    DB::rollBack();
                }

                $this->updateMeasures(new AapcrDetailMeasure(), $data['id'], $data['measures']);

                $officeModel = new AapcrDetailOffice();

                foreach ($formFields as $formField) {
                    $this->updateOffices([
                        'form' => 'aapcr',
                        'model' => $officeModel,
                        'detailId' => $data['id'],
                        'offices' => $data[$formField->code],
                        'type' => $formField->id,
                    ]);
                }

                if (isset($data['children']) && count((array)$data['children'])) {

                    foreach ($data['children'] as $child) {
                        $child['detailId'] = $detail->id;

                        $this->updateDetails($child, $aapcrId);
                    }
                }
            } else {
                DB::rollBack();
            }
        } else {
            $this->saveAapcrDetails($aapcrId, $data);
        }
    }

    public function updateProgramBudgets($aapcrId, $budgets)
    {
        $ids = array();

        foreach ($budgets as $budget) {
            $programBudget = AapcrProgramBudget::withTrashed()->where([
                'aapcr_id' => $aapcrId,
                'program_id' => $budget['mainCategory']['key']
            ])->first();

            if (!isset($programBudget->id)) {
                $newBudget = new AapcrProgramBudget();

                $newBudget->aapcr_id = $aapcrId;
                $newBudget->program_id = $budget['mainCategory']['key'];
                $newBudget->budget = $budget['categoryBudget'];
                $newBudget->create_id = $this->login_user->pmaps_id;
                $newBudget->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

                if (!$newBudget->save()) {
                    DB::rollBack();
                } else {
                    $ids[] = $newBudget->id;
                }
            } else {
                $history = "";

                if ($programBudget->trashed()) {
                    $programBudget->restore();
                    $history = "Added " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                $cachedBudget = $programBudget->budget;

                $programBudget->budget = $budget['categoryBudget'];

                if ($programBudget->isDirty('budget')) {
                    $history = "Updated budget from '" . $cachedBudget . "' to '" . $budget['categoryBudget'] . "' " . Carbon::now() . " by " . $this->login_user->fullname . "\n";
                }

                $programBudget->modify_id = $this->login_user->pmaps_id;
                $programBudget->history = $programBudget->history . $history;

                if (!$programBudget->save()) {
                    DB::rollBack();
                }

                $ids[] = $programBudget->id;
            }
        }

        $deletedIds = AapcrProgramBudget::where('aapcr_id', $aapcrId)->whereNotIn('id', $ids)->get();

        foreach ($deletedIds as $deletedId) {
            $deletedId->updated_at = Carbon::now();
            $deletedId->modify_id = $this->login_user->pmaps_id;
            $deletedId->history = $deletedId->history . "Removed " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

            if (!$deletedId->save()) {
                DB::rollBack();
            } else {
                if (!$deletedId->delete()) {
                    DB::rollBack();
                }
            }
        }
    }

    public function aapcrChildDetails($aapcr_id)
    {

        return $this->getChildDetails($aapcr_id);
    }

    public function getChildDetails($aapcr_id)
    {
        $childDetails = AapcrDetail::where([
            ['aapcr_id', $aapcr_id],
            ['is_header', 0]
        ])
            ->with('subCategory', 'measures')
            ->get()
            ->groupBy('sub_category_id'); // Group by sub_category_id instead of subCategory.name

        $result = [];

        foreach ($childDetails as $subCategoryId => $details) {
            foreach ($details as $child) {
                $measures = $child->measures->map(function ($measure) {
                    return $measure->name; // Adjust this according to your actual measure field
                })->toArray();

                $implementingOffices = $this->aapcrImplementingOffice($child->id)->map(function ($office) {
                    return [
                        'id' => $office->id,
                        'office_name' => $office->office_name,
                    ];
                });
                $supportOffices = $this->aapcrSuuportOffice($child->id)->map(function ($office) {
                    return [
                        'id' => $office->id,
                        'office_name' => $office->office_name,
                    ];
                });
                // Get the subCategory name to include in the result
                $subCategoryName = $child->subCategory->name ?? '';

                // Initialize subcategory array if not already set
                if (!isset($result[$subCategoryId])) {
                    $result[$subCategoryId]['sub_category_name'] = $subCategoryName;
                    $result[$subCategoryId]['details'] = [];
                }

                // Add details to the subcategory
                $result[$subCategoryId]['details'][] = [
                    'pi_name' => $child->pi_name,
                    'pi_name_id' => $child->id,
                    'target' => $child->target,
                    'target_basis' => $child->targets_basis,
                    'measures' => $measures,
                    'implementing_offices' => $implementingOffices,
                    'support_offices' => $supportOffices,
                    'other_remarks' => $child->other_remarks
                ];
            }
        }

        return $result;
    }


    public function aapcrImplementingOffice($parent_id)
    {
        $officeModel = new AapcrDetailOffice();
        return $officeModel->getImplementingOfficeDetails($parent_id);
    }

    public function aapcrSuuportOffice($parent_id)
    {
        $officeModel = new AapcrDetailOffice();
        return $officeModel->getSupportOfficeDetails($parent_id);
    }
}
