<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Support\CourseProgress;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeedDemoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['site.demo' => ['enabled' => true, 'password' => 'clave-demo-segura', 'email_base' => 'Profe@Ejemplo.MX']]);
    }

    private function demoUsers()
    {
        return User::where('email', 'like', 'profe+gg-%@ejemplo.mx');
    }

    public function test_refuses_without_demo_mode_or_password(): void
    {
        config(['site.demo.enabled' => false]);
        $this->artisan('app:seed-demo')->assertFailed();

        config(['site.demo.enabled' => true, 'site.demo.password' => null]);
        $this->artisan('app:seed-demo')->assertFailed();

        $this->assertSame(0, $this->demoUsers()->count());
    }

    public function test_seeds_staff_courses_and_students_and_is_idempotent(): void
    {
        $this->artisan('app:seed-demo')->assertSuccessful();

        $count = fn (string $role) => $this->demoUsers()->where('role_id', Role::where('slug', $role)->value('id'))->count();
        $this->assertSame(1, $count('director'));
        $this->assertSame(3, $count('ponente'));
        $this->assertSame(12, $count('alumno'));
        $this->assertTrue(Hash::check('clave-demo-segura', User::where('email', 'profe+gg-alumno01@ejemplo.mx')->value('password')));

        $courses = Course::where('slug', 'like', 'demo-%')->with('blocks.topics.tasks', 'materials')->get();
        $this->assertCount(3, $courses);
        $this->assertCount(3, $courses->pluck('ponente_id')->unique());
        foreach ($courses as $course) {
            $this->assertGreaterThanOrEqual(2, $course->blocks->count());
            $this->assertGreaterThanOrEqual(3, $course->blocks->flatMap->topics->filter(fn ($t) => strlen($t->content) > 150)->count());
            $this->assertGreaterThanOrEqual(3, $course->blocks->flatMap->topics->flatMap->tasks->count());
            $this->assertTrue($course->materials->contains('kind', 'file'));
            Storage::disk('local')->assertExists($course->materials->firstWhere('kind', 'file')->file_path);
        }
        $this->assertGreaterThanOrEqual(10, DB::table('enrollments')->whereIn('course_id', $courses->pluck('id'))->distinct()->count('user_id'));

        $snapshot = [User::count(), Course::count(), Submission::count(), DB::table('topic_progress')->count()];
        $this->artisan('app:seed-demo')->assertSuccessful();
        $this->assertSame($snapshot, [User::count(), Course::count(), Submission::count(), DB::table('topic_progress')->count()]);

        $this->artisan('app:seed-demo --reset')->assertSuccessful();
        $this->assertSame($snapshot, [User::count(), Course::count(), Submission::count(), DB::table('topic_progress')->count()]);
    }

    public function test_never_touches_a_super_admin(): void
    {
        $admin = User::factory()->role('super_admin')->create(['email' => 'profe+gg-director@ejemplo.mx', 'name' => 'Dueño real']);
        $hash = $admin->password;

        $this->artisan('app:seed-demo')->assertSuccessful();

        $admin->refresh();
        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertSame($hash, $admin->password);
        $this->assertSame('Dueño real', $admin->name);
    }

    public function test_progress_is_varied(): void
    {
        $this->artisan('app:seed-demo')->assertSuccessful();
        $course = Course::where('slug', 'demo-estadistica-descriptiva')->sole();
        $student = fn (string $tag) => User::where('email', "profe+gg-$tag@ejemplo.mx")->sole();

        $advanced = new CourseProgress($course, $student('alumno01'));
        $zero = new CourseProgress($course, $student('alumno05'));
        $topics = $course->blocks->flatMap->topics;
        $this->assertSame($topics->count(), $topics->filter(fn ($t) => $advanced->state($t) === 'completed')->count());
        $this->assertSame(0, $topics->filter(fn ($t) => $zero->state($t) === 'completed')->count());

        $statuses = Submission::whereIn('user_id', $this->demoUsers()->pluck('id'))->pluck('status');
        foreach (['graded', 'submitted', 'returned'] as $status) {
            $this->assertContains($status, $statuses);
        }
        $this->assertNotEmpty($course->pendingTasksFor($student('alumno04')));
        $this->assertTrue(Submission::where('status', 'graded')->get()->contains(fn ($s) => $s->needsReinforcement()));
    }
}
