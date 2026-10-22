<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

/** Bloques, temas y materiales se autorizan con la Policy de su curso. Tabla de permisos: docs/PLAN.md, sección 3. */
class CoursePolicy
{
    public function view(User $user, Course $course): bool
    {
        if ($user->hasRole('super_admin', 'director') || $this->owns($user, $course)) {
            return true;
        }

        return $user->hasRole('alumno') && $course->status === 'published' && $course->hasReader($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('ponente', 'super_admin');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->hasRole('super_admin') || $this->owns($user, $course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }

    public function manageStudents(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }

    private function owns(User $user, Course $course): bool
    {
        return $user->hasRole('ponente') && $course->ponente()->is($user);
    }
}
