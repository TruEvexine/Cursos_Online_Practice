@props(['course'])
<span {{ $attributes }}>{{ $course->catalog_price > 0 ? '$'.number_format($course->catalog_price, 0).' MXN' : 'Gratis' }}</span>
