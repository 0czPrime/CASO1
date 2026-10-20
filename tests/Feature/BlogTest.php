<?php

namespace Tests\Feature;

use App\Models\Entrada;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function entradaDe(User $autor, string $titulo = 'Convocatoria 2026', int $categoria = 1): Entrada
    {
        $entrada = new Entrada([
            'categoria_id' => $categoria,
            'titulo' => $titulo,
            'contenido' => 'Abierta la convocatoria del semillero.',
        ]);
        $entrada->autor_id = $autor->id;
        $entrada->save();

        return $entrada;
    }

    private function datosEntrada(array $cambios = []): array
    {
        return array_merge([
            'titulo' => 'Titulo valido',
            'categoria_id' => 1,
            'contenido' => 'Contenido suficientemente largo.',
        ], $cambios);
    }

    public function test_pr01_registro_valido_guarda_solo_el_hash(): void
    {
        $this->post('/registro', ['name' => 'Ana Perez', 'email' => 'ana@correo.com', 'password' => 'clave12345'])
            ->assertRedirect('/login');

        $usuario = User::where('email', 'ana@correo.com')->first();
        $this->assertNotNull($usuario);
        $this->assertNotSame('clave12345', $usuario->password);
        $this->assertTrue(Hash::check('clave12345', $usuario->password));
    }

    public function test_pr02_correo_duplicado_es_rechazado(): void
    {
        User::factory()->create(['email' => 'ana@correo.com']);

        $this->post('/registro', ['name' => 'Otra Ana', 'email' => 'ana@correo.com', 'password' => 'clave12345'])
            ->assertSessionHasErrors('email');
    }

    public function test_pr03_contrasena_corta_es_rechazada(): void
    {
        $this->post('/registro', ['name' => 'Pedro Ruiz', 'email' => 'pedro@correo.com', 'password' => '123'])
            ->assertSessionHasErrors('password');
    }

    public function test_pr04_login_con_clave_incorrecta_no_crea_sesion(): void
    {
        User::factory()->create(['email' => 'ana@correo.com', 'password' => 'clave12345']);

        $this->post('/login', ['email' => 'ana@correo.com', 'password' => 'mala'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_pr05_login_correcto_crea_la_sesion(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@correo.com', 'password' => 'clave12345']);

        $this->post('/login', ['email' => 'ana@correo.com', 'password' => 'clave12345'])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_pr06_crear_entrada_valida_asigna_el_autor_de_la_sesion(): void
    {
        $ana = User::factory()->create();
        $otro = User::factory()->create();

        $this->actingAs($ana)->post('/entradas', $this->datosEntrada(['autor_id' => $otro->id]))
            ->assertRedirect();

        $this->assertDatabaseHas('entradas', ['titulo' => 'Titulo valido', 'autor_id' => $ana->id]);
    }

    public function test_pr07_titulo_vacio_o_categoria_inexistente_son_rechazados(): void
    {
        $ana = User::factory()->create();

        $this->actingAs($ana)->post('/entradas', $this->datosEntrada(['titulo' => '', 'categoria_id' => 999]))
            ->assertSessionHasErrors(['titulo', 'categoria_id']);
        $this->assertDatabaseCount('entradas', 0);
    }

    public function test_pr08_filtrar_por_categoria(): void
    {
        $ana = User::factory()->create();
        $this->entradaDe($ana, 'Aviso de convocatoria', 1);
        $this->entradaDe($ana, 'Aviso de evento', 2);

        $this->get('/?categoria=2')->assertSee('Aviso de evento')->assertDontSee('Aviso de convocatoria');
    }

    public function test_pr09_el_autor_edita_su_entrada_y_no_cambia_el_autor(): void
    {
        $ana = User::factory()->create();
        $otro = User::factory()->create();
        $entrada = $this->entradaDe($ana);

        $this->actingAs($ana)->put('/entradas/'.$entrada->id, $this->datosEntrada(['titulo' => 'Titulo actualizado', 'autor_id' => $otro->id]))
            ->assertRedirect();

        $entrada->refresh();
        $this->assertSame('Titulo actualizado', $entrada->titulo);
        $this->assertSame($ana->id, (int) $entrada->autor_id);
    }

    public function test_pr10_otro_usuario_no_puede_abrir_la_edicion_de_una_entrada_ajena(): void
    {
        $entrada = $this->entradaDe(User::factory()->create());

        $this->actingAs(User::factory()->create())->get('/entradas/'.$entrada->id.'/editar')->assertForbidden();
    }

    public function test_pr11_edicion_ajena_con_solicitud_manipulada_devuelve_403(): void
    {
        $ana = User::factory()->create();
        $intruso = User::factory()->create();
        $entrada = $this->entradaDe($ana);

        $this->actingAs($intruso)->put('/entradas/'.$entrada->id, $this->datosEntrada(['titulo' => 'Hackeado', 'autor_id' => $intruso->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('entradas', ['id' => $entrada->id, 'titulo' => 'Convocatoria 2026', 'autor_id' => $ana->id]);
    }

    public function test_pr12_eliminacion_ajena_devuelve_403_y_la_entrada_permanece(): void
    {
        $entrada = $this->entradaDe(User::factory()->create());

        $this->actingAs(User::factory()->create())->delete('/entradas/'.$entrada->id)->assertForbidden();

        $this->assertDatabaseHas('entradas', ['id' => $entrada->id]);
    }

    public function test_pr13_visitante_sin_sesion_es_enviado_al_login(): void
    {
        $entrada = $this->entradaDe(User::factory()->create());

        $this->get('/entradas/crear')->assertRedirect('/login');
        $this->get('/entradas/'.$entrada->id.'/editar')->assertRedirect('/login');
        $this->put('/entradas/'.$entrada->id, $this->datosEntrada())->assertRedirect('/login');
    }

    public function test_pr15_comentario_con_script_se_muestra_como_texto(): void
    {
        $entrada = $this->entradaDe(User::factory()->create());
        $lector = User::factory()->create();

        $this->actingAs($lector)->post('/entradas/'.$entrada->id.'/comentarios', ['texto' => '<script>alert(1)</script>Hola'])
            ->assertRedirect();

        $this->get('/entradas/'.$entrada->id)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;Hola', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_pr16_comentario_vacio_o_muy_largo_es_rechazado(): void
    {
        $entrada = $this->entradaDe(User::factory()->create());
        $lector = User::factory()->create();

        $this->actingAs($lector)->post('/entradas/'.$entrada->id.'/comentarios', ['texto' => ''])->assertSessionHasErrors('texto');
        $this->actingAs($lector)->post('/entradas/'.$entrada->id.'/comentarios', ['texto' => str_repeat('a', 1001)])->assertSessionHasErrors('texto');
    }

    public function test_pr17_texto_con_sql_se_guarda_literal(): void
    {
        $ana = User::factory()->create();
        $sql = "x'); DROP TABLE entradas;--";

        $this->actingAs($ana)->post('/entradas', $this->datosEntrada(['titulo' => $sql]))->assertRedirect();

        $this->assertDatabaseHas('entradas', ['titulo' => $sql]);
        $this->assertDatabaseCount('entradas', 1);
    }
}
