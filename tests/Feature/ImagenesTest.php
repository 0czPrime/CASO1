<?php

namespace Tests\Feature;

use App\Models\Entrada;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImagenesTest extends TestCase
{
    use RefreshDatabase;

    private const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private function png(string $nombre = 'foto.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nombre, base64_decode(self::PNG_1X1));
    }

    private function datos(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'Titulo valido',
            'categoria_id' => 1,
            'contenido' => 'Contenido suficientemente largo.',
        ], $extra);
    }

    public function test_pr18_el_autor_sube_una_imagen_valida(): void
    {
        Storage::fake('public');
        $ana = User::factory()->create();

        $this->actingAs($ana)->post('/entradas', $this->datos(['imagen' => $this->png()]))->assertRedirect();

        $entrada = Entrada::firstOrFail();
        $this->assertNotNull($entrada->imagen);
        Storage::disk('public')->assertExists($entrada->imagen);
    }

    public function test_pr19_un_archivo_que_no_es_imagen_es_rechazado(): void
    {
        Storage::fake('public');
        $ana = User::factory()->create();
        $falso = UploadedFile::fake()->createWithContent('shell.php', '<?php echo "hola";');

        $this->actingAs($ana)->post('/entradas', $this->datos(['imagen' => $falso]))->assertSessionHasErrors('imagen');

        $this->assertDatabaseCount('entradas', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_pr20_una_imagen_de_mas_de_2mb_es_rechazada(): void
    {
        Storage::fake('public');
        $ana = User::factory()->create();
        $grande = UploadedFile::fake()->create('grande.png', 3000, 'image/png');

        $this->actingAs($ana)->post('/entradas', $this->datos(['imagen' => $grande]))->assertSessionHasErrors('imagen');

        $this->assertDatabaseCount('entradas', 0);
    }

    public function test_pr21_el_nombre_guardado_no_es_el_del_usuario(): void
    {
        Storage::fake('public');
        $ana = User::factory()->create();

        $this->actingAs($ana)->post('/entradas', $this->datos(['imagen' => $this->png('mi-foto-secreta.png')]));

        $this->assertStringNotContainsString('mi-foto-secreta', Entrada::firstOrFail()->imagen);
    }

    public function test_pr22_otro_usuario_no_puede_cambiar_la_imagen_de_una_entrada_ajena(): void
    {
        Storage::fake('public');
        $ana = User::factory()->create();
        $intruso = User::factory()->create();

        $this->actingAs($ana)->post('/entradas', $this->datos(['imagen' => $this->png()]));
        $entrada = Entrada::firstOrFail();
        $rutaOriginal = $entrada->imagen;

        $this->actingAs($intruso)->put('/entradas/'.$entrada->id, $this->datos(['imagen' => $this->png('otra.png')]))->assertForbidden();

        $this->assertSame($rutaOriginal, $entrada->fresh()->imagen);
        Storage::disk('public')->assertExists($rutaOriginal);
    }

    public function test_pr23_al_eliminar_la_entrada_se_borra_su_imagen(): void
    {
        Storage::fake('public');
        $ana = User::factory()->create();

        $this->actingAs($ana)->post('/entradas', $this->datos(['imagen' => $this->png()]));
        $entrada = Entrada::firstOrFail();
        $ruta = $entrada->imagen;

        $this->actingAs($ana)->delete('/entradas/'.$entrada->id)->assertRedirect();

        Storage::disk('public')->assertMissing($ruta);
        $this->assertDatabaseCount('entradas', 0);
    }
}
