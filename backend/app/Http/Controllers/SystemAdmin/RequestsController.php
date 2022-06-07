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

class RequestsController extends Controller
{
    use PdfTrait;

    private $login_user;

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

            DB::beginTransaction();

            $request = FormUnpublishStatus::find($id);

            $original = $request->getOriginal();

            $request->status = $status;
            $request->changed_date = Carbon::now();
            $request->changed_by = $this->login_user->fullName;
            $request->updated_at = Carbon::now();
            $request->modify_id = $this->login_user->fullName;

            $history = "Updated status from '" . $original['status'] . "' to '" . $status . "' " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            $request->history .= $request->history . $history;

            if($status === 'verified'){
                $model = null;
                switch ($original['form_type']) {
                    case 'aapcr':
                        $model = new Aapcr();
                        $filename = $this->viewAapcrPdf($original['form_id'], 1);
                        $request->file_name = $filename;
                        break;
                    case 'vpopcr':
                        $model = new VpOpcr();
                        $filename = $this->viewVpOpcrPdf($original['form_id'], 1);
                        $request->file_name = $filename;
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

    public function unpublishedForm($model, $id, $user)
    {
        $form = $model::find($id);

        $form->published_date = NULL;
        $form->updated_at = Carbon::now();
        $form->modify_id = $this->login_user->pmaps_id;
        $form->history = $form->history . "Unpublished " . Carbon::now() . " by " . $user . "\n";

        $form->save();
    }

    public function viewUnpublishedForm($id)
    {
        $form = FormUnpublishStatus::find($id);

        $storage = storage_path('app/public/uploads/published/' . $form->file_name);

        return response()->download($storage);
    }
}
