<?php

namespace App\Http\Traits;

use App\Aapcr;
use App\AapcrDetail;
use App\AapcrDetailOffice;
use App\Http\Classes\Jasperreport;
use App\Program;
use App\Signatory;
use App\VpOpcr;
use App\VpOpcrDetailOffice;
use Illuminate\Support\Facades\Storage;
use PHPJasper\PHPJasper;

trait PdfTrait {
    use ConverterTrait;

    private $vpProgramDataSet= [];

    public function checkDirectories()
    {
        $jsonPath = public_path('storage/json');
        $rawPath = public_path('storage/raw');
        $uploadsPath = public_path('storage/uploads');

        $uploadsPublishedPath = public_path('storage/uploads/published');

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
    }

    // AAPCR
    public function viewAapcrPdf($id, $isUnpublish=0)
    {
        $aapcr = Aapcr::find($id);

        $documentName = $aapcr->document_name;

        $officeModel = new AapcrDetailOffice();

        $signatory = $this->getSignatories($aapcr, "aapcr");

        $programs = Program::where('year', $aapcr->year)->orderBy('category_id', 'asc')->get();

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

            $subCategory = ($detail->sub_category_id ? $detail->subCategory->name : NULL);

            $measures = $this->fetchMeasuresPdf($detail->measures);

            $getOffices = $this->getOfficesPdf($officeModel, $detail->id);

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
                'parentSubCategory' => $detail->sub_category_id ? $detail->subCategory->parent_id : NULL,
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

            if($detail->sub_category_id !== NULL && $detail->subCategory->parent_id !== NULL) {
                $this->parentSubCategories = [];

                $this->getParentSubCategories($detail->subCategory->parent_id);

                $reversedSubCategories = array_reverse($this->parentSubCategories);

                foreach($reversedSubCategories as $dataKey => $subParent) {
                    $subCategoryKey = "subCategoryParent_".($dataKey+1);

                    $data[$PICount][$subCategoryKey] = $subParent;
                }
            }

            $PICount++;

            $subPis = $aapcr->details()->where('parent_id', $detail->id)->get();

            if(count($subPis)){
                foreach($subPis as $subKey => $subPi) {

                    $subMeasures = $this->fetchMeasuresPdf($subPi->measures);

                    $getSubOffices = $this->getOfficesPdf($officeModel, $subPi->id);

                    $data[$PICount] = array(
                        'category_id' => $detail->category_id,
                        'category_order' => $detail->category->order,
                        'function' => $function,
                        'program' => $detail->program->name,
                        'subCategory' => $subCategory,
                        'parentSubCategory' => $detail->sub_category_id ? $detail->subCategory->parent_id : NULL,
                        'pi_name' => $subPi->pi_name,
                        'target' => $subPi->target,
                        'measures' => implode(", ", $subMeasures),
                        'allocatedBudget' => $subPi->allocated_budget ? number_format($subPi->allocated_budget) : '',
                        'targetsBasis' => $subPi->targets_basis,
                        'implementing' => implode(', ', $getSubOffices['implementing']),
                        'supporting' => implode(', ', $getSubOffices['supporting']),
                        'otherRemarks' => $subPi->other_remarks,
                        'subPICount' => $subKey+1
                    );

                    if($detail->sub_category_id !== NULL && $detail->subCategory->parent_id !== NULL) {
                        foreach($reversedSubCategories as $key => $subParent) {
                            $subCategoryKey = "subCategoryParent_".($key+1);

                            $data[$PICount][$subCategoryKey] = $subParent;
                        }
                    }

                    $PICount++;
                }
            }
        }

        $publicPath = public_path();

        $params = [
            'usepLogo' => $publicPath."/logos/USeP_Logo.png",
            'notFinal' => $isUnpublish ? $publicPath."/logos/unpublished.png" : (!$aapcr->published_date || !$aapcr->is_active ? $publicPath."/logos/notfinal.png" : ""),
            'totalBudget' => number_format($totalBudget, 0),
            'year' => $aapcr->year,
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
            'isUnpublish' => $isUnpublish,
            'form' => 'aapcr',
            'id' => $id,
            'jsonArrayData' => ['main' => $data, 'programsDataSet' => $programsDataSet],
            'params' => $params,
        ];

        $file = $this->renderPDFFile($pdfData);

        if(!$isUnpublish) {
            return response()->download($file)->deleteFileAfterSend();
        } else { return $file; }
    }

    // VP's OPCR
    public function viewVpOpcrPdf($id, $isUnpublish=0)
    {
        $vpopcr = VpOpcr::find($id);

        $officeId = $vpopcr->office_id;

        $signatory = $this->getSignatories($vpopcr, 'vpopcr');

        $documentName = str_replace(" ","_", $vpopcr->office_name);

        $details = $vpopcr->details()->where('parent_id', NULL)
            ->orderBy('category_id', 'ASC')
            ->orderByRaw('-program_id DESC')
            ->orderByRaw('!ISNULL(sub_category_id), sub_category_id ASC')
            ->orderByRaw('-`aapcr_detail_id` DESC')
            ->orderBy('created_at', 'ASC')->get();

        $dataSource = [];

        $parentIds = []; // To store parent PIs' id for tracking purposes

        foreach($details as $detail) {
            $stored = 0;

            $isParent = 0;

            $parentDetails = null;

            $data = $this->getVpOpcrPdfDetails($detail, []);

            if($detail->aapcr_detail_id) {
                $aapcrDetail = $detail->aapcrDetail;

                if($aapcrDetail->parent_id && $detail->from_aapcr) {
                    if(!$detail->aapcrDetail->parent->is_header) {
                        $parentDetail = AapcrDetail::whereHas('offices', function($query) use ($officeId) {
                            $query->where(function($q) use ($officeId) {
                                $q->where('vp_office_id', '=', $officeId)
                                    ->orWhere('office_id', '=', $officeId);
                            });
                        })->with(['measures', 'offices'])->where('id', $aapcrDetail->parent_id)->first();

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

                        $parentDetails->program = null;

                        $parentDetails->sub_category = null;
                    }else{
                        $parentDetails->sub_category = $detail->sub_category_id;
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
            'notFinalImage' => $isUnpublish ? $publicPath."/logos/unpublished.png" : (!$vpopcr->published_date || !$vpopcr->is_active ? $publicPath."/logos/notfinal.png" : ""),
            'year' => $vpopcr->year,
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
            'isUnpublish' => $isUnpublish,
            'form' => 'vpopcr',
            'id' => $id,
            'jsonArrayData' => ['main' => $dataSource, 'programsDataSet' => $this->vpProgramDataSet],
            'params' => $params,
        ];

        $file = $this->renderPDFFile($pdfData);

        if(!$isUnpublish) {
            return response()->download($file)->deleteFileAfterSend();
        }else { return $file; }
    }

    public function getVpOpcrPdfDetails($detail, $children)
    {
        $officeModel = new VpOpcrDetailOffice();

        $function = $this->integerToRomanNumeral($detail->category->order) . ". " . mb_strtoupper($detail->category->name);

        $program = $detail->program ? $detail->program->name : null;

        $measures = '' ;

        $getOffices = [];

        if(!$detail->is_header) {
            $measures = $this->fetchMeasuresPdf($detail->measures);

            if(isset($detail->isParent) && $detail->isParent) {
                $officeModel = new AapcrDetailOffice();
            }

            $getOffices = $this->getOfficesPdf($officeModel, $detail->id);
        }

        $data = array(
            'id' => $detail->id,
            'category_id' => $detail->category_id,
            'category_order' => $detail->category->order,
            'function' => $function,
            'program' => $program,
            'subCategory' => $detail->sub_category_id ? $detail->subCategory->name : NULL,
            'parentSubCategory' => $detail->sub_category_id ? $detail->subCategory->parent_id : NULL,
            'pi_name' => $detail->pi_name,
            'target' => $detail->target,
            'measures' => $measures ? implode(", ", $measures) : '',
            'allocatedBudget' => $detail->allocated_budget ? number_format($detail->allocated_budget) : '',
            'targetsBasis' => $detail->targets_basis,
            'implementing' => isset($getOffices['implementing']) ? implode(", ", $getOffices['implementing']) : '',
            'supporting' => isset($getOffices['supporting']) ? implode(", ", $getOffices['supporting']) : '',
        );

        if($detail->sub_category_id !== NULL && $detail->subCategory->parent_id !== NULL) {
            $this->parentSubCategories = [];

            $this->getParentSubCategories($detail->subCategory->parent_id);

            $reversedSubCategories = array_reverse($this->parentSubCategories);

            foreach($reversedSubCategories as $dataKey => $subParent) {
                $subCategoryKey = "subCategoryParent_".($dataKey+1);

                $data[$subCategoryKey] = $subParent;
            }
        }

        if(count($children)){
            $data['children'][] = $children;
        }else {
            $data['children'] = null;
        }

        # OVER-ALL RATING DETAILS
        $ifSaved = $this->array_any(function($x, $compare){
            return $x['programName'] === ucwords($compare['progName']);
        }, $this->vpProgramDataSet, ['progName' => strtolower($program)]);

        if(!$ifSaved && $detail->program) {
            $categoryName = $this->integerToRomanNumeral($detail->category->order) . ". " . mb_strtoupper($detail->category->name);

            $this->vpProgramDataSet[] = array(
                'categoryName' => $categoryName,
                'categoryPercentage' => $detail->category->percentage,
                'programName' => ucwords($detail->program->name),
                'programPercentage' => $detail->program->percentage
            );
        }

        return $data;
    }

    public function getSignatories($model, $form)
    {
        if($form === 'vpopcr') {
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
            $name = $PIMeasure->name;
            $itemNames = [];
            if($PIMeasure->display_as_items) {
                foreach($PIMeasure->items as $item) {
                    $itemNames[] = $item->rate . " - " . $item->description;
                }

                $name = "\n" . implode("\n", $itemNames);
            }

            $measures[] = $name;
        }

        return $measures;
    }

    public function getOfficesPdf($model, $detailId)
    {
        $getOffices = $model::select('office_name', 'office_type_id', 'is_group', 'group_id')
            ->where('detail_id', $detailId)
            ->orderBy('office_type_id', 'asc')
            ->get();

        $allOffices = [
            'implementing' => array(),
            'supporting' => array()
        ];

        foreach($getOffices as $getOffice) {
            $officeName = $getOffice['office_name'];

            if ($getOffice['is_group']) {
                $officeName = $getOffice->group->name;
            }

            //if($getOffice['office_type_id'] === 'implementing') {
            $allOffices[$getOffice->field->code][] = $officeName;
            /*}else{
                array_push($allOffices['supporting'], $officeName);
            }*/
        }

        return $allOffices;
    }

    public function renderPDFFile($pdfData=[])
    {
        extract($pdfData);

        $this->checkDirectories();

        $extension = 'pdf' ;
        $input = public_path() . '/raw/' . $form . '.jasper';



        if(!$isUnpublish) {
            $filename =  $documentName  . "_". date("Ymd");
            $output = base_path('/public/forms/' . $filename);
        } else{
            $filename =  $documentName;
            $output = storage_path('app/public/uploads/published/' . $filename);
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

        $file = $output .'.'.$extension ;

        if (!file_exists($file)) {
            abort(404);
        }else if($isUnpublish) {
            $file = $filename .'.'.$extension;
        }

        return $file;
    }
}
