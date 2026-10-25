<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SchoolQuote;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_home_is_public_with_login_card_and_honest_status(): void
    {
        config(['site.contact_email' => 'hola@ejemplo.mx', 'site.whatsapp' => '524520000000']);

        $this->get('/')->assertOk()
            ->assertSee('Clases en línea, sin enredos')
            ->assertSee('action="'.route('login').'"', false)
            ->assertSee('Ejemplo, sujetas a cambio')
            ->assertSee('Próximamente')
            ->assertSee('href="https://wa.me/524520000000" target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('WhatsApp 452 000 0000')
            ->assertSee('mailto:hola@ejemplo.mx', false)
            ->assertSee('<meta property="og:title"', false)
            ->assertDontSee('Ir a mi panel');
    }

    public function test_signed_in_user_sees_go_to_panel(): void
    {
        $this->actingAs(User::factory()->role('ponente')->create())->get('/')->assertOk()
            ->assertSee('Ir a mi panel')->assertSee(route('dashboard'), false)->assertDontSee('¿Olvidaste tu contraseña?');
    }

    public function test_login_from_home_shows_errors_on_home_and_login_page_still_works(): void
    {
        $user = User::factory()->role('alumno')->create();

        $this->from('/')->post('/login', ['email' => $user->email, 'password' => 'incorrecta'])->assertRedirect('/');
        $this->get('/')->assertSee('aria-invalid="true"', false)->assertSee(trans('auth.failed'));

        $this->get('/login')->assertOk()->assertSee('Iniciar sesión');
        $this->from('/')->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_footer_links_respond(): void
    {
        foreach (['/sobre-nosotros' => 'Universidad Vasco de Quiroga', '/aviso-de-privacidad' => 'Provisional', '/terminos' => 'Menores de edad'] as $url => $text) {
            $this->get($url)->assertOk()->assertSee($text);
        }
        $this->assertStringContainsString('User-agent: *', file_get_contents(public_path('robots.txt')));
        $html = $this->get('/')->getContent();
        foreach (['funcionalidades', 'roles', 'planes', 'contacto', 'preguntas', 'para-quien'] as $anchor) {
            $this->assertStringContainsString('id="'.$anchor.'"', $html);
        }
    }

    public function test_school_simulator_formula_and_validation(): void
    {
        $this->assertSame(4250, SchoolQuote::monthly(250, 5)); // 1500 + 150×15 + 2×250
        $this->assertSame(1500, SchoolQuote::monthly(0, 0));
        $this->assertSame(1500, SchoolQuote::monthly(100, 3));
        $this->assertNull(SchoolQuote::monthly(1001, 5));
        $this->assertNull(SchoolQuote::monthly(10, 31));

        $this->get('/?alumnos=250&ponentes=5')->assertOk()->assertSee('$4,250 al mes');
        $this->get('/?alumnos=1001&ponentes=5')->assertSee('plan a medida');
        $this->get('/?alumnos=abc&ponentes=-1')->assertSee('Escribe un número entero.')->assertSee('Escribe un número de 0 a 5,000.')->assertDontSee('al mes</strong>', false);
        $this->get('/?alumnos=5001&ponentes=1')->assertSee('Escribe un número de 0 a 5,000.');
    }
}
