<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Services\AttachementService;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    protected $attachservice;
    /**
     * StudentController constructor
     *
     * @param Attachementservice $attachservice
     */
    public function __construct(AttachementService $attachservice)
    {
        $this->attachservice = $attachservice;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */  public function store($task, $attachement)
    {
        // Create a new attachement in the database
        $result = $this->attachservice->storeAttachement($task, $attachement);

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the created attachement
        return $this->success($result);
    }

    /**
     * Display the specified resource.
     */
    public function show(Attachment $attachment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attachment $attachment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attachment $attachment)
    {
        //
    }
}
