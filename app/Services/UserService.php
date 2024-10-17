<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\CacheException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{
    /**
     * Return all users
     *
     * This method retrieves all users from the database and returns them in a
     * paginated format. The users are cached for 3600 seconds (1 hour) to
     * reduce the load on the database.
     *
     * @return bool|\App\Models\User
     */
    public function All()
    {
        try {
            // Store the users in cache for 1 hour
            $users = Cache::remember('users',3600,function(){
                // Retrieve all users from the database
                return  User::select('name', 'email','role')->paginate();
            });

            // Return the users
            return $users;
        } catch (CacheException $e) {
            // Log caching errors
            Log::error('Cache error'. $e->getMessage());
            // Return false if there was an error
            return false;
        } catch (QueryException $e) {
            // Log database errors
            Log::error('Database error'.$e->getMessage());
            // Return false if there was an error
            return false;
        } catch (Exception $e) {
            // Log the error message
            Log::error('Error message: ' . $e->getMessage());
            // Return the error message
            return false;
        }
    }


    /**
     * Create a new user
     *
     * @param array $data
     * @return bool|\App\Models\User
     */
    public function Create(array $data)
    {
        try {
            // Create a new instance of the user model
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
            ]);

            // Flush the users cache
            Cache::forget('users');

            // Return the user
            return $user;
        } catch (CacheException $e) {
            // caching errors
            Log::error('Cache error'. $e->getMessage());
            return false;
        } catch (QueryException $e) {
            // Log the error message
            Log::error('Database error' . $e->getMessage());
            // Return false if the user wasn't created
            return false;
        } catch (Exception $e) {
            // Log the error message
            Log::error('An unexpected error occurred' . $e->getMessage());
            return false;
        }
    }
    /**
     * Update a user
     *
     * This method updates a user in the database
     *
     * @param array $data The data to update
     * @param int $user The user ID
     *
     * @return bool|\App\Models\User The user info
     */
    public function Update(array $data, $user)
    {
        try{
            // Retrieve the user from the database
            $user = User::findOrFail($user);
            // Update the user
            $user->update($data);

            // Return the user
            return $user;
        } catch (ModelNotFoundException $e) {
            // Handle the case where the user is not found
            Log::error('User not found' . $e->getMessage());
            // Return false if the user wasn't found
            return false;
        }  catch (QueryException $e) {
                // Log the error message
                Log::error('Database error' . $e->getMessage());
                // Return false if the user wasn't updated
                return false;
        }catch (Exception $e) {
            // Log the error message
            Log::error('error' . $e->getMessage());
            return false;
        }
    }
    /**
     * Show the user with the given ID
     *
     * This method retrieves a user from the database and returns the user info.
     * The user is cached for 150 seconds (2.5 minutes) to reduce the load on the
     * database.
     *
     * @param int $user The user ID
     * @return bool|array The user info
     */
    public function Show($user)
    {
        try {
            // Store the user in cache for 2.5 minutes
            $user = Cache::remember('user_'.$user,150,function()use ($user){
                // Retrieve the user from the database
                return User::select('name','email','role')->findOrFail($user);
            });

            // Return the user info
            return $user;
        } catch (ModelNotFoundException $e) {
            // Handle the case where the user is not found
            Log::error('User not found' . $e->getMessage());
            // Return false if the user wasn't found
            return false;
        } catch (Exception $e) {
            // Log the error message
            Log::error('error message' . $e->getMessage());
            // Return the error message
            return false;
        }
    }
    /**
     * Delete a user
     *
     * This method deletes a user from the database and returns
     * a success message if the deletion was successful.
     *
     * @param int $user The user ID
     * @return string|bool The success message or false if there was an error
     */
    public function Delete($user)
    {
        try{
            // Retrieve the user from the database
            $user = User::findOrFail($user);
            // Delete the user
            $user->delete();

            // Return a success message
            return 'deleted successfully';
        } catch (ModelNotFoundException $e) {
            // Handle the case where the user is not found
            Log::error('User not found' . $e->getMessage());
            // Return false if the user wasn't found
            return false;
        }  catch (QueryException $e) {
            // Log the error message
            Log::error('Database error' . $e->getMessage());
            // Return false if the user wasn't created
            return false;
        }catch (Exception $e) {
            // Log the error message
            Log::error('error' . $e->getMessage());
            // Return false if the customer wasn't created
            return false;
        }
    }

}
