<?php

namespace App\Http\Controllers;

use App\Http\Requests\user\StoreUserRequest;
use App\Http\Requests\user\UpdateUserRequest;
use App\Services\UserService;


class UserController extends Controller
{
    protected $userservice;
    /**
     * UserController constructor
     *
     * @param UserService $userservice
     */
    public function __construct(UserService $userservice)
    {
        $this->userservice = $userservice;
    }

    public function index()
    {
        // Get paginated list of users
        $result = $this->userservice->All();

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the paginated list
        return $this->paginated($result);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUserRequest $request)
    {
        // Validate the request input
        $validatedInput = $request->validated();

        // Create a new user in the database
        $result = $this->userservice->Create($validatedInput);

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the created user
        return $this->success($result);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param integer $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, $user)
    {
        // Validate the request input
        $validatedInput = $request->validated();
        $result = $this->userservice->Update($validatedInput, $user);
        if (!$result) {
            return $this->error();
        }
        return $this->success($result);
    }

    /**
     * Display the specified resource.
     *
     * Shows the specified user's details.
     *
     * @param integer $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($user)
    {
        $result = $this->userservice->Show($user);
        if (!$result) {
            return $this->error();
        }
        return $this->success($result);
    }
    /**
     * Remove the specified resource from storage.
     * @param integer $user
     */
    public function destroy($user)
    {
        $result = $this->userservice->Delete($user);
        if (!$result) {
            return $this->error();
        }
        return $this->success($result);
    }
}
