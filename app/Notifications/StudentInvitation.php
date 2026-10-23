<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Alumno nuevo: el enlace de recuperación de Fortify sirve como "fija tu contraseña"; al usarlo, el correo queda verificado. */
class StudentInvitation extends Notification
{
    public function __construct(public Course $course, public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = config('auth.passwords.users.expire');

        return (new MailMessage)
            ->subject('Te inscribieron en «'.$this->course->title.'»')
            ->greeting('Hola, '.$notifiable->name)
            ->line('Te inscribieron en el curso «'.$this->course->title.'» de '.config('app.name').'. Para entrar, primero fija tu contraseña.')
            ->action('Fijar mi contraseña', route('password.reset', ['token' => $this->token, 'email' => $notifiable->email]))
            ->line("Este enlace caduca en {$minutes} minutos. Si ya caducó, entra a ".route('login').' y elige «¿Olvidaste tu contraseña?» para pedir otro.')
            ->salutation('Saludos, '.config('app.name'));
    }
}
