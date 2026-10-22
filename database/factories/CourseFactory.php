<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ponente_id' => User::factory()->role('ponente'),
            'title' => fake()->sentence(3),
            'status' => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    /** Inscribe a un alumno con el estado dado. */
    public function enroll(User $student, string $status = 'active'): static
    {
        return $this->afterCreating(fn (Course $course) => $course->enrollments()->create(['user_id' => $student->id, 'status' => $status]));
    }
}
