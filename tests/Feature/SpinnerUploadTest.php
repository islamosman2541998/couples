<?php

namespace Tests\Feature;

use App\Models\{SpinnerImage, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SpinnerUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_placeholder_validation_and_image_replacement(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $item = SpinnerImage::create(['name' => 'Test', 'image' => 'spinner/placeholder.png', 'color' => '#ffffff']);
        $url = '/admin/spinner-images/'.$item->id;
        $this->get($url.'/edit')->assertOk()->assertDontSee('src="'.$item->image_url.'"', false);
        $data = ['name' => 'Test', 'color' => '#ffffff', 'sort_order' => 1, 'is_active' => 1];
        $this->from($url.'/edit')->put($url, $data + ['image' => UploadedFile::fake()->image('large.jpg')->size(2049)])->assertSessionHasErrors('image');
        $this->get($url.'/edit')->assertSee('لم يتم الحفظ');
        $this->assertSame('spinner/placeholder.png', $item->fresh()->image);
        $this->put($url, $data + ['image' => UploadedFile::fake()->image('valid.jpg')])->assertSessionHasNoErrors();
        $item->refresh();
        Storage::disk('public')->assertExists($item->image);
        $this->get($url.'/edit')->assertSee($item->image_url);
        $old = $item->image;
        $this->put($url, $data + ['image' => UploadedFile::fake()->image('replacement.jpg')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($item->fresh()->image);
    }
}
