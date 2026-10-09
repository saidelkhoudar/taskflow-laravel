<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TaskFlowTest extends TestCase
{
    use DatabaseMigrations;

    private function csrf(): array
    {
        $this->withSession(['_token' => 'test-csrf-token']);
        return ['_token' => 'test-csrf-token'];
    }

    public function test_guests_are_redirected_from_task_dashboard_and_mutations(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->post('/tasks', $this->csrf() + ['title' => 'Nope'])->assertRedirect('/login');
    }

    public function test_registration_logs_user_in(): void
    {
        $this->post('/register', $this->csrf() + [
            'name' => 'Amina', 'email' => 'amina@example.test',
            'password' => 'password-123', 'password_confirmation' => 'password-123',
        ])->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'amina@example.test']);
    }

    public function test_login_and_logout_work(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password-123')]);
        $this->post('/login', $this->csrf() + ['email' => $user->email, 'password' => 'password-123'])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout', $this->csrf())->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_invalid_login_is_rejected(): void
    {
        $this->post('/login', $this->csrf() + ['email' => 'nobody@example.test', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_owner_can_create_update_delete_and_filter_tasks(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->post('/tasks', $this->csrf() + ['title' => 'Ship portfolio', 'description' => 'Laravel docs', 'status' => 'todo', 'priority' => 'high'])
            ->assertRedirect('/');
        $task = Task::first();
        $this->assertSame($user->id, $task->user_id);
        $this->get('/?q=portfolio&status=todo')->assertOk()->assertSee('Ship portfolio');
        $this->put("/tasks/{$task->id}", $this->csrf() + ['title' => 'Ship portfolio', 'description' => 'Done', 'status' => 'done', 'priority' => 'high'])
            ->assertRedirect('/');
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'done']);
        $this->delete("/tasks/{$task->id}", $this->csrf())->assertRedirect('/');
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_users_cannot_view_update_or_delete_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::create(['user_id' => $owner->id, 'title' => 'Private', 'status' => 'todo', 'priority' => 'medium']);
        $this->actingAs($other);
        $this->get('/')->assertOk()->assertDontSee('Private');
        $this->put("/tasks/{$task->id}", $this->csrf() + ['title' => 'Stolen', 'status' => 'done', 'priority' => 'high'])
            ->assertNotFound();
        $this->delete("/tasks/{$task->id}", $this->csrf())->assertNotFound();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Private']);
    }

    public function test_invalid_task_data_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $this->from('/')->post('/tasks', $this->csrf() + ['title' => '', 'status' => 'invalid', 'priority' => 'urgent'])
            ->assertRedirect('/')->assertSessionHasErrors(['title', 'status', 'priority']);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_state_changing_forms_render_csrf_tokens_and_use_web_middleware(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->get('/')->assertOk()->assertSee('name="_token"', false);
        $this->assertContains('web', app('router')->getRoutes()->getByName('tasks.store')->gatherMiddleware());
        $this->assertContains('web', app('router')->getRoutes()->getByName('logout')->gatherMiddleware());
    }
}
