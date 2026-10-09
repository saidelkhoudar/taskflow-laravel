<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Tests\TestCase;

class TaskFlowTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_dashboard_loads(): void
    {
        $this->get('/')->assertOk()->assertSee('Make progress visible.');
    }

    public function test_valid_task_can_be_created(): void
    {
        $this->post('/tasks', ['title'=>'Ship portfolio','description'=>'Write docs','status'=>'todo','priority'=>'high'])
            ->assertRedirect('/');
        $this->assertDatabaseHas('tasks', ['title'=>'Ship portfolio','priority'=>'high']);
    }

    public function test_invalid_task_is_rejected(): void
    {
        $this->from('/')->post('/tasks', ['title'=>'','status'=>'invalid','priority'=>'urgent'])
            ->assertRedirect('/')->assertSessionHasErrors(['title','status','priority']);
        $this->assertDatabaseCount('tasks', 0);
    }
}
