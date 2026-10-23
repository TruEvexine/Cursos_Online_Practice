<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Role;
use App\Models\User;
use App\Notifications\StudentInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function index(Course $course)
    {
        Gate::authorize('manageStudents', $course);
        $enrollments = $course->enrollments()->with('user')
            ->orderByRaw("FIELD(status, 'active', 'completed', 'removed')")->orderByDesc('enrolled_at')->get();

        return view('students.index', ['course' => $course, 'enrollments' => $enrollments]);
    }

    /**
     * Alumno existente → se inscribe (o se reactiva si estaba quitado). Otro rol → se rechaza.
     * Correo nuevo → cuenta de alumno + enlace para fijar su contraseña.
     */
    public function store(StudentRequest $request, Course $course)
    {
        $email = Str::lower(trim($request->validated('email')));
        $student = User::withTrashed()->where('email', $email)->first();

        if ($student && ($student->trashed() || ! $student->hasRole('alumno'))) {
            throw ValidationException::withMessages(['email' => 'Ese correo es de una cuenta que no es de alumno o que está dada de baja; no se puede inscribir.']);
        }
        if (! $student && ! $request->filled('name')) {
            throw ValidationException::withMessages(['name' => 'Ese correo todavía no tiene cuenta: escribe el nombre del alumno para crearla.']);
        }

        $enrollment = $student ? $course->enrollments()->firstOrNew(['user_id' => $student->id]) : null;
        if ($enrollment?->exists && $enrollment->status !== 'removed') {
            return redirect()->route('students.index', $course)->with('status', "{$student->name} ya está en el curso.");
        }

        DB::transaction(function () use ($course, &$student, &$enrollment, $email, $request) {
            if (! $student) {
                // Contraseña aleatoria que nadie conoce: el alumno fija la suya con el enlace del correo.
                $student = new User(['name' => $request->validated('name'), 'email' => $email, 'password' => Str::random(64)]);
                $student->role_id = Role::where('slug', 'alumno')->value('id');
                $student->save();
                $enrollment = $course->enrollments()->make(['user_id' => $student->id]);
            }
            $enrollment->fill(['status' => 'active', 'enrolled_at' => now(), 'removed_at' => null])->save();
        });

        if ($student->wasRecentlyCreated) {
            $student->notify(new StudentInvitation($course, Password::broker()->createToken($student)));

            return redirect()->route('students.index', $course)->with('status', "Inscribimos a {$student->name} y le enviamos un correo para fijar su contraseña.");
        }

        return redirect()->route('students.index', $course)->with('status', "Inscribimos a {$student->name}.");
    }

    /** "Quitar" conserva la fila y su historial; queda en la bitácora. */
    public function destroy(Enrollment $enrollment)
    {
        $course = $enrollment->course ?? abort(404);
        Gate::authorize('manageStudents', $course);

        if ($enrollment->status !== 'removed') {
            $enrollment->update(['status' => 'removed', 'removed_at' => now()]);
            AuditLog::record('enrollment.removed', $enrollment, ['course_id' => $course->id, 'student_id' => $enrollment->user_id]);
        }

        return redirect()->route('students.index', $course)->with('status', 'Quitamos a '.$enrollment->user->name.' del curso.');
    }
}
