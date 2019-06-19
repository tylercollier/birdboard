<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectsTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /**
     * @test
     */
    public function a_user_can_create_a_project() {
        $this->withoutExceptionHandling();
        $this->actingAs(factory('App\User')->create());
        $attributes = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph
        ];
        $this->post('/projects', $attributes)->assertRedirect('/projects');
        $this->assertDatabaseHas('projects', $attributes);
        $this->get('/projects')->assertSee($attributes['title']);
    }

    public function test_a_project_requires_a_title() {
        $this->actingAs(factory('App\User')->create());
        $attributes = factory('App\Project')->raw(['title' => '']);
        $this->post('/projects', $attributes)->assertSessionHasErrors('title');
    }

    public function test_a_project_requires_a_description() {
        $this->actingAs(factory('App\User')->create());
        $this->post('/projects', [])->assertSessionHasErrors('description');
    }

    public function test_a_user_can_view_a_project() {
        $project = factory('App\Project')->create();
        $this->actingAs($project->owner);
        $this->get($project->path())
            ->assertSee($project->title)
            ->assertSee($project->description);
    }

    public function test_a_user_cannot_view_another_users_project() {
        $project = factory('App\Project')->create();
        $this->actingAs(factory('App\User')->create());
        $this->get($project->path())->assertForbidden();
    }

    public function test_a_project_requires_an_owner() {
        $attributes = factory('App\Project')->raw();
        $this->post('/projects', $attributes)->assertRedirect('login');
    }

    public function test_only_authenticated_users_can_view_projects() {
        $this->get('/projects')->assertRedirect('login');
    }
}
