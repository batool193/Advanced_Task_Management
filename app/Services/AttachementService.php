<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Illuminate\Support\Facades\Log;

class AttachementService
{
    /**
     * Store an attachment for a task.
     *
     * @param $task
     * @param Request $attachment
     * @return string|bool
     */
    public function storeAttachment($task, Request $attachment)
    {
        try {
            // Validate the attachment
            $attachment->validate([
                'file' => 'required|file|mimes:pdf,doc,docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:10240', // Max file size: 10MB
            ]);

            $file = $attachment->file('file');
            $originalName = $file->getClientOriginalName();

            // Check for disallowed characters in the file name
            if (preg_match('/\.[^.]+\./', $originalName) || strpos($originalName, '..') !== false || strpos($originalName, '/') !== false || strpos($originalName, '\\') !== false) {
                throw new Exception(trans('general.notAllowedAction'), 403);
            }

            $allowedMimeTypes = ['application/pdf','doc','docx','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $mime_type = $file->getClientMimeType();

            // Check if the file type is allowed
            if (!in_array($mime_type, $allowedMimeTypes)) {
                throw new FileException(trans('general.invalidFileType'), 403);
            }

            $fileName = Str::random(32);
            $extension = $file->getClientOriginalExtension();
            $filePath = "public/uploads/{$fileName}.{$extension}";

            $scan = new VirusTotalService();
            $scanResult = $scan->scanFile($file);

            // Check if the file is malicious
            if (isset($scanResult['data']['attributes']['last_analysis_stats']['malicious']) && $scanResult['data']['attributes']['last_analysis_stats']['malicious'] > 0) {
                Log::error('File is malicious');
                return false;
            }

            $path = Storage::disk('local')->putFileAs('public/uploads', $file, $fileName . '.' . $extension);
            $url = Storage::url($path);

            $user = JWTAuth::user();
            $attachment = $task->attachments()->create([
                'file_name' => $fileName,
                'file_path' => $url,
                'attach_by' => $user->id,
            ]);

            return $url; // Return the URL of the stored attachment
        } catch (ValidationException $e) {
            Log::error('Validation error: ' . $e->getMessage());
            return false;
        } catch (FileException $e) {
            Log::error('File error: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return false;
        }
    }


    public function All() {}

    public function Update(array $data, $user) {}

    public function Show($user) {}
    public function Delete($user) {}
}
