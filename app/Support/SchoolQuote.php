<?php

namespace App\Support;

/** Único lugar donde se calcula el plan Escuela. null = plan a medida (hay que contactarnos). */
class SchoolQuote
{
    public static function monthly(int $students, int $teachers): ?int
    {
        $p = config('site.school');

        if ($students > $p['custom_above_students'] || $teachers > $p['custom_above_teachers']) {
            return null;
        }

        return $p['base']
            + max(0, $students - $p['included_students']) * $p['per_extra_student']
            + max(0, $teachers - $p['included_teachers']) * $p['per_extra_teacher'];
    }
}
