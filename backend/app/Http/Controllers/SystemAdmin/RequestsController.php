<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Aapcr;
use App\FormUnpublishStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRequestStatus;
use App\Http\Traits\PdfTrait;
use App\VpOpcr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;

class RequestsController extends Controller
{
    use PdfTrait;

    private $login_user;

    public function __construct()
    {

        $this->middleware(function ($request, $next) {
            $this->login_user = Auth::user();

            return $next($request);
        });

    }

    public function getAllUnpublishRequests($status)
    {
        $unpublishList = FormUnpublishStatus::select("*", 'id as key')
                                            ->where('status', $status)->orderBy('created_at', 'desc')
                                            ->with('form:id,abbreviation')
                                            ->get();

        return response()->json([
            'unpublishList' => $unpublishList
        ], 200);
    }

    public function updateFormRequestStatus(UpdateRequestStatus $request)
    {
        try {
            $validated = $request->validated();

            $id = $validated['id'];
            $status = $validated['status'];
            $fileName = $validated['fileName'] ?? null;

            DB::beginTransaction();

            $request = FormUnpublishStatus::find($id);

            $original = $request->getOriginal();

            $request->status = $status;
            $request->file_name = null;
            $request->changed_date = Carbon::now();
            $request->changed_by = $this->login_user->fullname;
            $request->updated_at = Carbon::now();
            $request->modify_id = $this->login_user->fullname;

            $history = "Updated status from '" . $original['status'] . "' to '" . $status . "' " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

            $request->history .= $request->history . $history;

            if($status === 'verified'){
                $model = null;
                switch ($original['form_type']) {
                    case 'aapcr':
                        $model = new Aapcr();
                        $this->pdfWatermark($fileName);
                        $request->file_name = $fileName;
                        break;
                    case 'vpopcr':
                        $model = new VpOpcr();
                        $this->pdfWatermark($fileName);
                        $request->file_name = $fileName;
                        break;
                    default:
                        break;
                }

                $this->unpublishedForm($model, $original['form_id'], $request->requested_by);
            }

            if(!$request->save()) {
                DB::rollBack();
            }

            DB::commit();

            return response()->json('Request was ' . $status . ' successfully', 200);

        }catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function pdfWatermark($fileName)
    {
        $filePath = storage_path('app/public/uploads/published/' . $fileName);

        $outputFilePath = storage_path('app/public/uploads/unpublished/' . $fileName);

        $watermarkImg = public_path("/logos/WM_Unpublished.png");

        $pdf = new Fpdi();

        if(file_exists($filePath)){
            $pagecount = $pdf->setSourceFile($filePath);
        }else{
            abort(404,'Source PDF not found!');
        }

        for($i=1;$i<=$pagecount;$i++){
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->addPage();
            $pdf->useTemplate($tpl, 1, 1, $size['width'], $size['height'], TRUE);

            //Put the watermark
            $pdf->Image($watermarkImg, 35, 15, 0, 0, 'png');
        }

        $pdf->Output('F', $outputFilePath);
    }

    public function unpublishedForm($model, $id, $user)
    {
        $form = $model::find($id);

        $form->published_date = NULL;
        $form->published_file = NULL;
        $form->updated_at = Carbon::now();
        $form->modify_id = $this->login_user->pmaps_id;
        $form->history = $form->history . "Unpublished " . Carbon::now() . " by " . $user . "\n";

        $form->save();
    }

    public function viewUnpublishedForm($id)
    {
        $form = FormUnpublishStatus::find($id);

        $storage = storage_path('app/public/uploads/unpublished/' . $form->file_name);

        return response()->download($storage);
    }
}
