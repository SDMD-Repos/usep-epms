<?php

namespace App\Http\Traits;

use App\Aapcr;
use App\AapcrDetail;
use App\Measure;
use App\MeasureRating;
use App\Program;
use App\Signatory;
use App\VpOpcr;
use Illuminate\Support\Facades\Storage;
use PHPJasper\PHPJasper;
use PDF;
use LynX39\LaraPdfMerger\Facades\PdfMerger;

trait PdfTrait {
    use ConverterTrait;

    private $vpProgramDataSet= [];

    public function checkDirectories()
    {
        $jsonPath = public_path('storage/json');
        $rawPath = public_path('storage/raw');
        $uploadsPath = public_path('storage/uploads');

        $uploadsPublishedPath = public_path('storage/uploads/published');

        $publishedRatingScalesPath = public_path('storage/uploads/published/scales');

        if(!Storage::exists($jsonPath)) {
            Storage::makeDirectory('public/json', 0777, true, true);
        }

        if(!Storage::exists($rawPath)) {
            Storage::makeDirectory('public/raw', 0777, true, true);
        }

        if(!Storage::exists($uploadsPath)) {
            Storage::makeDirectory('public/uploads', 0777, true, true);
        }

        if(!Storage::exists($uploadsPublishedPath)) {
            Storage::makeDirectory('public/uploads/published', 0777, true, true);
        }

        if(!Storage::exists($publishedRatingScalesPath)) {
            Storage::makeDirectory('public/uploads/published/scales', 0777, true, true);
        }
    }

    public function viewSavedPdf($fileName)
    {
        $file = storage_path('app/public/uploads/published/' . $fileName);

        return response()->download($file);
    }

    // AAPCR
    public function viewAapcrPdf($id, $isPublish=0)
    {
        try {
            $aapcr = Aapcr::find($id);

            $documentName = $aapcr->document_name;

            $year = $aapcr->year;

            $signatory = $this->getSignatories($aapcr, "aapcr");

            $programs = Program::where('year', $year)->whereNull('form_id')->orderBy('category_id', 'asc')->get();

            $programsDataSet = array();

            foreach ($programs as $programKey => $program) {
                $categoryName = $this->integerToRomanNumeral($program->category->order) . ". " . mb_strtoupper($program->category->name);

                $programsDataSet[$programKey] = array(
                    'categoryName' => $categoryName,
                    'categoryPercentage' => $program->category->percentage,
                    'programName' => $program->name,
                    'programPercentage' => $program->percentage
                );
            }
            $data[0] = array();

            $details = $aapcr->details()->where('parent_id', NULL)
                ->orderBy('category_id', 'ASC')
                ->orderBy('program_id', 'ASC')
                ->orderBy('sub_category_id', 'ASC')->get();

            $PICount = 0;

            $aapcrBudgets = $aapcr->budgets;

            $tempProgramId = 0;

            $totalBudget = 0;

            foreach($details as $detail) {
                $budget = 0;

                $function = $this->integerToRomanNumeral($detail->category->order) . ". " . mb_strtoupper($detail->category->name);

                $detailSubCategory = $detail->subCategory;

                $countChildSubCategory = $detail->sub_category_id ? count($detail->subCategory->childSubCategories) : 0;

                $subCategory = (($detail->sub_category_id && !$countChildSubCategory) ? $detailSubCategory->name : NULL);

                $parentSubCategory = $detail->sub_category_id ? $detailSubCategory->parent_id : NULL;

                $reversedSubCategories = [];

                $measures = $this->fetchMeasuresPdf($detail->measures);

                $getOffices = $this->getOfficesPdf($detail->offices);

                if(!$tempProgramId || $tempProgramId !== $detail->program_id){
                    $tempProgramId = $detail->program_id;

                    foreach ($aapcrBudgets as $aapcrBudget) {
                        if($aapcrBudget->program_id === $detail->program_id){
                            $budget = $aapcrBudget->budget;
                            $totalBudget += $aapcrBudget->budget;
                            break;
                        }
                    }
                }

                $data[$PICount] = array(
                    'category_id' => $detail->category_id,
                    'category_order' => $detail->category->order,
                    'function' => $function,
                    'program' => $detail->program->name,
                    'subCategory' => $subCategory,
                    'parentSubCategory' => $parentSubCategory,
                    'pi_name' => $detail->pi_name,
                    'target' => $detail->target,
                    'measures' => implode(", ", $measures),
                    'allocatedBudget' => $detail->allocated_budget ? number_format($detail->allocated_budget) : '',
                    'targetsBasis' => $detail->targets_basis,
                    'implementing' => implode(", ", $getOffices['implementing']),
                    'supporting' => implode(", ", $getOffices['supporting']),
                    'programBudget' => $budget,
                    'otherRemarks' => $detail->other_remarks,
                    'subPICount' => 0
                );

                if($detail->sub_category_id !== NULL && $detailSubCategory->parent_id !== NULL && !$countChildSubCategory) {
                    $this->parentSubCategories = [];

                    $this->getParentSubCategories($detailSubCategory->parent_id);

                    $reversedSubCategories = array_reverse($this->parentSubCategories);

                    foreach($reversedSubCategories as $dataKey => $subParent) {
                        $subCategoryKey = "subCategoryParent_".($dataKey+1);

                        $data[$PICount][$subCategoryKey] = $subParent;
                    }
                }else if($countChildSubCategory){
                    $data[$PICount]['subCategoryParent_1'] = $detailSubCategory->name;
                }

                $PICount++;

                $subIndicators = $aapcr->details()->where('parent_id', $detail->id)->get();

                if(count($subIndicators)){
                    foreach($subIndicators as $subKey => $subIndicator) {

                        $subMeasures = $this->fetchMeasuresPdf($subIndicator->measures);

                        $getSubOffices = $this->getOfficesPdf($subIndicator->offices);

                        $data[$PICount] = array(
                            'category_id' => $detail->category_id,
                            'category_order' => $detail->category->order,
                            'function' => $function,
                            'program' => $detail->program->name,
                            'subCategory' => $subCategory,
                            'parentSubCategory' => $parentSubCategory,
                            'pi_name' => $subIndicator->pi_name,
                            'target' => $subIndicator->target,
                            'measures' => implode(", ", $subMeasures),
                            'allocatedBudget' => $subIndicator->allocated_budget ? number_format($subPi->allocated_budget) : '',
                            'targetsBasis' => $subIndicator->targets_basis,
                            'implementing' => implode(', ', $getSubOffices['implementing']),
                            'supporting' => implode(', ', $getSubOffices['supporting']),
                            'otherRemarks' => $subIndicator->other_remarks,
                            'subPICount' => $subKey+1
                        );

                        if(count($reversedSubCategories)) {
                            foreach($reversedSubCategories as $key => $subParent) {
                                $subCategoryKey = "subCategoryParent_".($key+1);

                                $data[$PICount][$subCategoryKey] = $subParent;
                            }
                        }elseif($countChildSubCategory){
                            $data[$PICount]['subCategoryParent_1'] = $detailSubCategory->name;
                        }

                        $PICount++;
                    }
                }
            }

            $publicPath = public_path();

            $params = [
                'usepLogo' => $publicPath."/logos/USeP_Logo.png",
                'notFinal' => ((!$aapcr->published_date || !$aapcr->is_active) && !$isPublish ? $publicPath."/logos/notfinal.png" : ""),
                'totalBudget' => number_format($totalBudget, 0),
                'year' => $year,
                'preparedBy' => strtoupper($signatory['preparedBy']),
                'preparedByPosition' => $signatory['preparedByPosition'],
                'preparedDate' => $signatory['preparedDate'],
                'reviewedBy' => $signatory['reviewedBy'],
                'reviewedByPosition' => $signatory['reviewedByPosition'],
                'reviewedDate' => $signatory['reviewedDate'],
                'approvedBy' => strtoupper($signatory['approvedBy']),
                'approvedDate' => $signatory['approvedDate'],
                'approvingPosition' => $signatory['approvedByPosition'],
                'public_path' => $publicPath,
                'storage_path_public' => storage_path('app/public'),
            ];

            $pdfData = [
                'documentName' => $documentName,
                'isPublish' => $isPublish,
                'form' => 'aapcr',
                'id' => $id,
                'year' => $year,
                'jsonArrayData' => ['main' => $data, 'programsDataSet' => $programsDataSet],
                'params' => $params,
            ];

            $file = $this->renderPDFFile($pdfData);

            if(!$isPublish) {
                return response()->download($file)->deleteFileAfterSend();
            } else { return $file; }
        } catch(\Exception $e){
            if($isPublish) {
               abort(404, "Please contact the administrator! " . $e->getMessage());
            }else {
                if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                    $status = $e->getCode();
                } else {
                    $status = 400;
                }

                return response()->json($e->getMessage(), $status);
            }
        }
    }

    // VP's OPCR
    public function viewVpOpcrPdf($id, $isPublish=0)
    {
        try {
            $vpopcr = VpOpcr::find($id);

            $officeId = $vpopcr->office_id;

            $year = $vpopcr->year;

            $signatory = $this->getSignatories($vpopcr, 'vpopcr');

            $documentName = str_replace(" ","_", $vpopcr->office_name);

            $details = $vpopcr->details()->where('parent_id', NULL)
                ->orderBy('category_id', 'ASC')
                ->orderByRaw('-program_id DESC')
                ->orderByRaw('!ISNULL(sub_category_id), sub_category_id ASC')
                ->orderBy('created_at', 'ASC')->get();

            $dataSource = [];

            $parentIds = []; // To store parent PIs' id for tracking purposes

            foreach($details as $detail) {
                $stored = 0;

                $isParent = 0;

                $parentDetails = null;

                $data = $this->getVpOpcrPdfDetails($detail, []);

                if($detail->aapcr_detail_id) {
                    $aapcrDetail = $detail->aapcrDetail()->with(['offices' => function($q) use ($officeId){
                        $q->filterVpOffices($officeId);
                    }])->first();

                    if($aapcrDetail->parent_id && $detail->from_aapcr) {
                        if(!$detail->aapcrDetail->parent->is_header) {
                            $parentDetail = AapcrDetail::whereHas('offices', function($query) use ($officeId) {
                                $query->filterVpOffices($officeId);
                            })->with(['measures', 'offices' => function ($queryOffice) use ($officeId) {
                                $queryOffice->filterVpOffices($officeId);
                            }])->where('id', $aapcrDetail->parent_id)->first();

                            if($parentDetail) {
                                $isParent = 1;

                                $parentDetail->isParent = 1;

                                $parentDetail->category_id = $detail->category_id;

                                $parentDetail->sub_category_id = $detail->sub_category_id;

                                $parentDetail->program_id = $detail->program_id;

                                $parentDetails = $parentDetail;

                            }else{
                                $stored = 1;
                            }
                        }else {
                            $isParent = 1;

                            $detail->aapcrDetail->parent->isParent = 1;

                            $parentDetails = $detail->aapcrDetail->parent;
                        }
                    } else {
                        if(!$detail->from_aapcr) {
                            $isParent = 1;

                            $aapcrDetail->isParent = 1;

                            $parentDetails = $aapcrDetail;
                        }else{

                            $stored = 1;
                        }
                    }
                } else {
                    $stored = 1;
                }

                if($isParent) {
                    $isExists = 0;

                    foreach($parentIds as $parentId) {
                        if($parentId['id'] === $parentDetails->id && $detail->category_id === $parentId['index']) {
                            $isExists = 1;
                        }
                    }

                    if(!$isExists) {
                        if($parentDetails->category_id !== $detail->category_id){
                            $parentDetails->category_id = $detail->category_id;

                            $parentDetails->program = $detail->program;

                            $parentDetails->sub_category_id = null;
                        }else{
                            $parentDetails->sub_category_id = $detail->sub_category_id;
                        }

                        $dataSource[] = $this->getVpOpcrPdfDetails($parentDetails, $data);

                        $parentIds[] = [
                            'id' => $parentDetails->id,
                            'index' => $detail->category_id
                        ];
                    } else {
                        foreach($dataSource as $key => $source) {
                            if($source['id'] === $parentDetails->id && $detail->category_id == $source['category_id']) {
                                $dataSource[$key]['children'][] = $data;
                            }
                        }
                    }
                } elseif($stored) {
                    if(count($detail->subDetails)) {
                        $subs = [];

                        foreach($detail->subDetails as $subDetail) {

                            $subs[] = $this->getVpOpcrPdfDetails($subDetail, []);
                        }

                        $data['children'] = $subs;
                    }

                    $dataSource[] = $data;

                    $parentIds[] = array(
                        'id' => $detail->id,
                        'index' => $detail->category_id
                    );
                }
            }

            $publicPath = public_path();

            $params = array(
                'usepLogo' => $publicPath."/logos/USeP_Logo.png",
                'public_path' => $publicPath,
                'notFinalImage' => ((!$vpopcr->published_date || !$vpopcr->is_active) && !$isPublish ? $publicPath."/logos/notfinal.png" : ""),
                'year' => $year,
                'vpOfficeName' => $vpopcr->office_name,
                'preparedBy' => strtoupper($signatory['preparedBy']),
                'preparedByPosition' => $signatory['preparedByPosition'],
                'preparedDate' => $signatory['preparedDate'],
                'reviewedBy' => $signatory['reviewedBy'],
                'reviewedByPosition' => $signatory['reviewedByPosition'],
                'reviewedDate' => $signatory['reviewedDate'],
                'approvedBy' => strtoupper($signatory['approvedBy']),
                'approvedDate' => $signatory['approvedDate'],
                'approvingPosition' => $signatory['approvedByPosition'],
                'assessedBy' => 'NAME:',
                'assessedByPosition' => 'PMT-PMG Member/Secretariat',
            );

            $pdfData = [
                'documentName' => $documentName,
                'isPublish' => $isPublish,
                'form' => 'vpopcr',
                'id' => $id,
                'year' => $year,
                'jsonArrayData' => ['main' => $dataSource, 'programsDataSet' => $this->vpProgramDataSet],
                'params' => $params,
            ];

            $file = $this->renderPDFFile($pdfData);

            if(!$isPublish) {
                return response()->download($file)->deleteFileAfterSend();
            }else { return $file; }
        } catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function getVpOpcrPdfDetails($detail, $children)
    {
        $function = $this->integerToRomanNumeral($detail->category->order) . ". " . mb_strtoupper($detail->category->name);

        $program = $detail->program ? $detail->program->name : null;

        $measures = '' ;

        $getOffices = [];

        if(!$detail->is_header) {
            $measures = $this->fetchMeasuresPdf($detail->measures);

            $getOffices = $this->getOfficesPdf($detail->offices);
        }

        $detailSubCategory = $detail->subCategory;

        $countChildSubCategory = $detail->sub_category_id ? count($detailSubCategory->childSubCategories) : 0;

        $subCategory = (($detail->sub_category_id && !$countChildSubCategory) ? $detailSubCategory->name : NULL);

        $reversedSubCategories = [];

        $data = array(
            'id' => $detail->id,
            'category_id' => $detail->category_id,
            'category_order' => $detail->category->order,
            'function' => $function,
            'program' => $program,
            'subCategory' => $subCategory,
            'parentSubCategory' => $detail->sub_category_id ? $detailSubCategory->parent_id : NULL,
            'pi_name' => $detail->pi_name,
            'target' => $detail->target,
            'measures' => $measures ? implode(", ", $measures) : '',
            'allocatedBudget' => $detail->allocated_budget ? number_format($detail->allocated_budget) : '',
            'targetsBasis' => $detail->targets_basis,
            'implementing' => isset($getOffices['implementing']) ? implode(", ", $getOffices['implementing']) : '',
            'supporting' => isset($getOffices['supporting']) ? implode(", ", $getOffices['supporting']) : '',
        );

        if($detail->sub_category_id !== NULL && $detailSubCategory->parent_id !== NULL  && !$countChildSubCategory) {
            $this->parentSubCategories = [];

            $this->getParentSubCategories($detailSubCategory->parent_id);

            $reversedSubCategories = array_reverse($this->parentSubCategories);

            foreach($reversedSubCategories as $dataKey => $subParent) {
                $subCategoryKey = "subCategoryParent_".($dataKey+1);

                $data[$subCategoryKey] = $subParent;
            }
        }else if($countChildSubCategory){
            $data['subCategoryParent_1'] = $detailSubCategory->name;
        }

        if(count($children)){
            $data['children'][] = $children;
        }else {
            $data['children'] = null;
        }

        # OVER-ALL RATING DETAILS
        $ifSaved = false;
        if($program) {
            $ifSaved = $this->array_any(function($x, $compare){
                return strtolower($x['programName']) === $compare['progName'];
            }, $this->vpProgramDataSet, ['progName' => strtolower($program)]);
        }

        if(!$ifSaved && $detail->program) {
            $categoryName = $this->integerToRomanNumeral($detail->category->order) . ". " . mb_strtoupper($detail->category->name);

            $this->vpProgramDataSet[] = array(
                'categoryName' => $categoryName,
                'categoryPercentage' => $detail->category->percentage,
                'programName' => ucwords($detail->program->name),
                'programPercentage' => $detail->program->percentage
            );
        }
    }

    public function viewCascadedVpOpcr()
    {

    }

    public function getSignatories($model, $form)
    {
        if($form === 'vpopcr' || $form === 'opcr') {
            $signatories = Signatory::where([
                ['year', $model->year],
                ['form_id', $form],
                ['office_form_id', $model->office_id]
            ])->get();
        } else {
            $signatories = Signatory::where([
                ['year', $model->year],
                ['form_id', $form]
            ])->get();
        }

        $signatoryList = [];

        foreach($signatories as $signatory) {
            $code = $signatory->type->code;

            $dataSignatories = [
                'name' => strtoupper($signatory['personnel_name']),
                'position' => $signatory['position'],
                'office_name' => $signatory['office_name'],
            ];

            if(!array_key_exists($code, $signatoryList)) {
                $signatoryList[$code] = [];
            }

            array_push($signatoryList[$code], $dataSignatories);
        }

        $preparedDate = ($model->finalized_date ? date("d F Y", strtotime($model->finalized_date)) : '');
        if(isset($signatoryList['prepared_by'])) {
            $preparedBy = $signatoryList['prepared_by'][0];

            $preparedByName = $preparedBy['name'];

            if($form === 'vpopcr') {
                $preparedByPosition = str_replace('Office of the ', '', $preparedBy['office_name']);
            } else {
                $preparedByPosition = $preparedBy['position'] . ", " . $preparedBy['office_name'];
            }
        } else {
            $preparedByName = "";
            $preparedByPosition = "";
        }

        $reviewedDate = ($model->reviewed_date ? date("d F Y", strtotime($model->reviewed_date)) : '');

        if(isset($signatoryList['reviewed_by'])) {
            $reviewedBy = $signatoryList['reviewed_by'][0];

            $reviewedByName = $reviewedBy['name'];

            if($form === 'vpopcr') {
                $reviewedByPosition = $reviewedBy['office_name'] . " " . $reviewedBy['position'];
            } else {
                $reviewedByPosition = $reviewedBy['position'] . ", " . $reviewedBy['office_name'];
            }
        } else {
            $reviewedByName = "";
            $reviewedByPosition = "";
        }

        $approvedDate = ($model->approved_date ? date("d F Y", strtotime($model->approved_date)) : '');

        if (isset($signatoryList['approved_by'])) {
            $approvedBy = $signatoryList['approved_by'][0];

            $approvedByName = $approvedBy['name'];
            $approvedByPosition = $approvedBy['position'];
        } else {
            $approvedByName = "";
            $approvedByPosition = "";
        }

        return [
            'preparedDate' => $preparedDate,
            'preparedBy' => $preparedByName,
            'preparedByPosition' => $preparedByPosition,
            'reviewedDate' => $reviewedDate,
            'reviewedBy' => $reviewedByName,
            'reviewedByPosition' => $reviewedByPosition,
            'approvedDate' => $approvedDate,
            'approvedBy' => $approvedByName,
            'approvedByPosition' => $approvedByPosition,
        ];
    }

    public function fetchMeasuresPdf($details)
    {
        $measures = array();

        foreach($details as $PIMeasure) {
            $itemNames = [];
            if($PIMeasure->display_as_items) {
                if($PIMeasure->is_custom) { $items = $PIMeasure->customItems; }
                else { $items = $PIMeasure->items;  }

                foreach($items as $item) {
                    $itemNames[] = $item->rating . " - " . $item->description;
                }

                $name = "\n" . implode("\n", $itemNames);
            }else {
                $name = $PIMeasure->name;
                if(!$PIMeasure->is_custom) { $name .= strtolower($PIMeasure->pivot->category->numbering); }
            }

            $measures[] = $name;
        }

        return $measures;
    }

    public function getOfficesPdf($offices)
    {
        $allOffices = [
            'implementing' => array(),
            'supporting' => array()
        ];

        foreach($offices as $office) {
            $officeName = $office['office_name'];

            if ($office['is_group']) {
                $officeName = $office->group->name;
            }

            $allOffices[$office->field->code][] = $officeName;
        }

        return $allOffices;
    }

    public function renderPDFFile($pdfData=[])
    {
        extract($pdfData);

        $this->checkDirectories();

        $extension = 'pdf' ;
        $input = public_path() . '/raw/' . $form . '.jasper';
        $publishedPath = storage_path('app/public/uploads/published/');

        if(!$isPublish) {
            $filename =  $documentName  . "_". date("Ymd");
            $output = base_path('/public/forms/' . $filename);
        } else{
            $filename =  strtoupper($form) . "_". $id . "_". time();
            $output = $publishedPath . $filename;
        }

        $jsonArry = array('data' => $jsonArrayData);
        $jsonTmpfilePath = storage_path('app/public/json/' . $filename . '.json');
        $jsonTmpfile = fopen($jsonTmpfilePath, 'w');
        fwrite($jsonTmpfile, json_encode($jsonArry));
        fclose($jsonTmpfile);
        $datafile = $jsonTmpfilePath;

        $options = [
            'format' => ['pdf'],
            'params' => $params,
            'locale' => 'en',
            'db_connection' => [
                'driver' => 'json',
                'data_file' => $datafile,
                'json_query' => 'data'
            ]
        ];

        $jasper = new PHPJasper();

        $jasper->compile(public_path() . '/raw/' . $form . '.jrxml')->execute();

        $jasper->process(
            $input,
            $output,
            $options
        )->execute();

        $file = $output .'.'.$extension;

        // Generate rating scale's PDF file
        $this->viewMeasurePDF($year, 1);

        $scalesFileName = 'scales/' . 'rating_scale_' . $year . '.pdf';
        $scalesFilePath = $publishedPath . $scalesFileName;

        if(Storage::disk('public')->exists('/uploads/published/' . $scalesFileName)) {
            // Merge Main Form's PDF and rating scale's PDF
            $pdfMerger = PDFMerger::init();

            $pdfMerger->addPDF($file);
            $pdfMerger->addPDF($scalesFilePath, '1');
            $pdfMerger->merge();
            $pdfMerger->save($file);
        }

        if (!file_exists($file)) {
            abort(404);
        }else if($isPublish) {
            $file = $filename .'.'.$extension;
        }

        return $file;
    }

    public function viewMeasurePDF($year, $save=0)
    {
        try {
            $measures = Measure::select('*', 'id as key')->where([
                ['year', $year], ['is_custom', 0]
            ])->with(['categories.items.rating'])->get();

            $ratings = MeasureRating::select("*", "id as key")->where('year', $year)->get();

            $tableColumnCount = 4;

            foreach($measures as $measure) { $tableColumnCount += count($measure->categories); }

            $data = ['measures' => $measures, 'ratings' => $ratings, 'tableColumnCount' => $tableColumnCount];

            $pdf = PDF::loadView('measurePdf', $data)->setPaper('folio', 'landscape');

            $filename = 'rating_scale_' . $year . '.pdf';

            if(!$save) {
                return $pdf->download($filename);
            }else {
                $pdf->save(storage_path('app/public/uploads/published/scales/' . $filename));
            }
        }catch (\Exception $e) {
            if (is_numeric($e->getCode()) && $e->getCode()) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }

    }
}
