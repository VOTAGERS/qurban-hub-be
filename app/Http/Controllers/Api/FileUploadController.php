<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
            'folder' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $folder = $request->input('folder', 'uploads/products');
            
            // Custom filename: timestamp_originalName
            $originalName = str_replace(' ', '_', $file->getClientOriginalName());
            $fileName = time() . '_' . $originalName;
            
            $path = $file->storeAs($folder, $fileName, 'public');
            
            $fileUpload = FileUpload::create([
                'filename'   => $file->getClientOriginalName(),
                'path'       => $path,
                'extension'  => $file->getClientOriginalExtension(),
                'mime_type'  => $file->getMimeType(),
                'size'       => $file->getSize(),
                'created_by' => auth()->user()->email ?? 'SYSTEM',
                'updated_by' => 'SYSTEM'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data'    => $fileUpload
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $fileUpload = FileUpload::findOrFail($id);
            Storage::disk('public')->delete($fileUpload->path);
            $fileUpload->delete();

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
