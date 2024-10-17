<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskStatusUpdate;
use App\Services\TaskStatusUpdateService;

class TaskStatusUpdateController extends Controller
{
    protected $statusservice;
    /**
     * TaskStatusUpdate constructor
     *
     * @param TaskStatusUpdateService $taskservice
     */
    public function __construct(TaskStatusUpdateService $statusservice)
    {
        $this->statusservice = $statusservice;
    }
    /**
     * Send a daily report email to the configured email address
     */
    public function dailyReport()
    {
        $result = $this->statusservice->dailyReport();
        if (!$result) {
            return $this->error();
        }
        return $this->success($result);
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
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskStatusUpdate $taskStatusUpdate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskStatusUpdate $taskStatusUpdate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskStatusUpdate $taskStatusUpdate)
    {
        //
    }
}
