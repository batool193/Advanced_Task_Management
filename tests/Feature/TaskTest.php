<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_all_tasks_with_filters(): void
    {
        $user = User::factory()->create(['role'=>'admin']);
      $response = $this->actingAs($user, 'api') ->get('/api/tasks?type=bug');
    // $response = $this->actingAs($user, 'api') ->get('/api/tasks?priority=high');
    $response = $this->actingAs($user, 'api') ->get('/api/tasks');
        $response->assertStatus(200);
    }
    public function test_user_can_get_all_its_tasks(): void
    {
        $user = User::factory()->create(['role'=>'developer']);
    $response = $this->actingAs($user, 'api') ->get('/api/tasks');
        $response->assertStatus(200);
    }
    public function test_admin_can_create_new_task(): void
    {
        $user = User::factory()->create(['role'=>'admin']);
        $task = [
            'title'=>'title',
            'description'=>'description',
            'type'=>'bug',
             'priority'=>'low',
             //'dependent_task_ids'=>[1]
        ];
        $response = $this->actingAs($user, 'api') ->post('/api/tasks',$task);
        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks',$task);

    }
    public function test_create_new_task_validation_error(): void
    {
        $user = User::factory()->create(['role'=>'admin']);
        $response = $this->actingAs($user, 'api') ->post('/api/tasks',[
            'title'=>'title',
            'description'=>'description',
            'type'=>'invailed_type',
             'priority'=>'low',
        ]);
        $response->assertStatus(403);
        $response->assertInvalid('type');
    }
    public function test_admin_can_show_task(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $task = Task::factory()->create();

        $response = $this->actingAs($admin, 'api') ->get('/api/tasks/'.$task->id);
        $response->assertStatus(200);
    }
    public function test_user_can_update_task(): void
    {
        $user = User::factory()->create(['role'=>'manager']);
        $task = Task::factory()->create(['created_by'=>$user]);

        $response = $this->actingAs($user, 'api') ->put('/api/tasks/'.$task->id,[
            'title'=>'test',
        ]);
        $response->assertStatus(200);
    }
    public function test_user_can_update_task_status(): void
    {
        $user = User::factory()->create(['role'=>'tester']);
        $task = Task::factory()->create(['title'=>'update status','assigned_to'=>$user]);
        $response = $this->actingAs($user, 'api') ->put('/api/tasks/'.$task->id.'/status',[
            'status'=>'completed',
        ]);
        $response->assertStatus(200);
    }
    public function test_admin_can_delete_task(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $task = Task::factory()->create(['created_by'=>$admin->id]);
        $response = $this->actingAs($admin, 'api') ->delete('/api/tasks/'.$task->id.'/deletetask');
        $response->assertStatus(200);
    }
    public function test_admin_can_delete_task_permentaly(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $task = Task::factory()->create(['created_by'=>$admin->id,'deleted_at'=>'2024-10-12']);
        $response = $this->actingAs($admin, 'api') ->delete('/api/tasks/'.$task->id);
        $response->assertStatus(200);
     $this->assertDatabaseMissing('tasks', ['id' => $task->id ]);
    }

     public function test_manager_can_assign_user_to_task(): void
    {
        $admin = User::factory()->create(['role'=>'manager']);
        $user = User::factory()->create();
        $task = Task::factory()->create(['created_by'=>$admin->id,'assigned_to'=>null]);

        $response = $this->actingAs($admin, 'api')->post('/api/tasks/'.$task->id.'/assign/'.$user->id);
        $response->assertStatus(200);
    }

    public function test_admin_can_restore_task(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $task = Task::factory()->create(['created_by'=>$admin->id,'deleted_at'=>'2024-10-12']);

        $response = $this->actingAs($admin, 'api') ->post('/api/tasks/'.$task->id.'/restore');
        $response->assertStatus(200);
    }
    public function test_user_can_add_attachement_to_task(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $task = Task::factory()->create(['created_by'=>$admin->id]);
       //$file = UploadedFile::fake()->create('document.doc',1000);
       $file = UploadedFile::fake()->create('document.pdf',1000);
        $response = $this->actingAs($admin, 'api') ->post('/api/tasks/'.$task->id.'/attachment',['file'=>$file]);
        $response->assertStatus(200);
    }

    public function test_user_can_add_comment_to_task(): void
    {
        $admin = User::factory()->create(['role'=>'developer']);
        $task = Task::factory()->create(['created_by'=>$admin->id]);

        $response = $this->actingAs($admin, 'api') ->post('/api/tasks/'.$task->id.'/comment/'.'test comment');
        $response->assertStatus(200);
    }

      public function test_admin_can_get_blocked_tasks(): void
    {
        $user = User::factory()->create(['role'=>'admin']);
    $response = $this->actingAs($user, 'api') ->get('/api/tasks/blocked');
        $response->assertStatus(200);
    }

}
