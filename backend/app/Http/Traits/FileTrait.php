<?php

namespace App\Http\Traits;

use Carbon\Carbon;

trait FileTrait {
    public function uploadFiles($model, $id, $files)
    {
        foreach($files as $file) {
            $fileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            $name = str_replace("." . $fileExtension, "", $fileName);

            $modFileName = $name . "__" . time() . "." . $fileExtension;

            $filePath = $file->storeAs('uploads', $modFileName, 'public');

            $model->form_id = $id;
            $model->file_path = $filePath;
            $model->file_name = $fileName;
            $model->create_id = $this->login_user->pmaps_id;
            $model->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            $model->save();
        }
    }

    public function viewFile($model, $id)
    {
        $file = $model::find($id);

        $contents = public_path('storage/' . $file->file_path);
        $headers = array('Content-Type: application/pdf',);

        return [
            'contents' => $contents,
            'headers' => $headers,
        ];
    }

    public function processUpdateFile($data=array())
    {
        $fileModel = $data['fileModel'];

        $formModel = $data['formModel'];

        $validated = $data['validated'];

        $files = $validated['files'];
        $id = $validated['id'];

        $now = Carbon::now();

        $formFile = $fileModel::find($id);

        $formId = $formFile->form_id;

        $formFile->updated_at = $now;
        $formFile->modify_id = $this->login_user->pmaps_id;
        $formFile->history = $formFile->history . "Deleted " . $now . " by " . $this->login_user->fullName . "\n";

        $formFile->save();

        $formFile->delete();

        $this->uploadFiles($fileModel, $formId, $files);

        return $formModel::where('id', $formId)->with('files')->first();

    }
}
