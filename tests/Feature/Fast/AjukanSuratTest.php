<?php

use App\Models\JenisSurat;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

function createSubmissionTemplate(JenisSurat $jenisSurat): void
{
    $template = $jenisSurat->template()->create([
        'name' => 'Template '.$jenisSurat->nama,
        'slug' => 'template-'.$jenisSurat->id,
        'format' => 'html',
        'template_body' => '<p>{{keperluan}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    $template->placeholders()->create([
        'placeholder_key' => 'keperluan',
        'label' => 'Keperluan',
        'source_type' => 'surat',
        'source_key' => 'keperluan',
        'is_required' => true,
    ]);
}

test('mahasiswa and dosen can view ajukan surat page', function (string $roleName, string $roleSlug, string $identifierLabel) {
    $this->withoutVite();

    $user = createUserWithType($roleSlug, [
        'nim_nip' => '2109012344',
        'name' => 'Adrian Muhammad',
    ]);

    $this->actingAs($user)
        ->get(route('fast.user.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('fast/user/Dashboard')
            ->where('userProfile.name', 'Adrian Muhammad')
            ->where('userType.label', $roleName)
            ->where('userProfile.identifierLabel', $identifierLabel)
            ->where('summary.total', 0)
        );
})->with([
    'mahasiswa' => ['Mahasiswa', 'mahasiswa', 'NIM'],
    'dosen' => ['Dosen', 'dosen', 'NIP'],
]);

test('role outside mahasiswa and dosen cannot access ajukan surat page', function () {
    $user = createUserWithType('administrasi');

    $this->actingAs($user)
        ->get(route('fast.user.dashboard'), ['Accept' => 'application/json'])
        ->assertForbidden();
});

test('academic user can submit surat with png attachment', function () {
    Storage::fake('public');

    $user = createUserWithType('dosen', [
        'name' => 'Rina Pratama',
        'nim_nip' => '1987001122',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Izin Penelitian',
        'slug' => 'surat-izin-penelitian',
        'is_active' => true,
        'field_config' => [],
        'alur_pengajuan' => 'submission',
    ]);
    createSubmissionTemplate($jenisSurat);

    $this->actingAs($user)
        ->post(route('fast.user.submissions.store'), [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Pengajuan surat untuk kebutuhan penelitian dosen pembimbing lapangan.',
            'tanggal_kebutuhan' => now()->addDays(3)->toDateString(),
            'lampiran' => [UploadedFile::fake()->image('surat.png')],
        ])
        ->assertRedirect(route('fast.user.dashboard'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('jenis_surats', [
        'nama' => 'Surat Izin Penelitian',
    ]);

    $this->assertDatabaseHas('surats', [
        'pemohon_id' => $user->id,
        'keperluan' => 'Pengajuan surat untuk kebutuhan penelitian dosen pembimbing lapangan.',
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('surat_lampirans', [
        'nama_file' => 'surat.png',
        'tipe' => 'image/png',
    ]);

    $path = DB::table('surat_lampirans')->value('file_path');
    Storage::disk('public')->assertExists($path);
});

test('ajukan surat validates invalid attachment format', function () {
    Storage::fake('public');

    $user = createUserWithType('mahasiswa');

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keterangan Aktif Kuliah',
        'slug' => 'surat-keterangan-aktif-kuliah',
        'is_active' => true,
        'field_config' => [],
        'alur_pengajuan' => 'submission',
    ]);
    createSubmissionTemplate($jenisSurat);

    $this->actingAs($user)
        ->post(route('fast.user.submissions.store'), [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Pengajuan surat untuk melengkapi administrasi kampus semester berjalan.',
            'tanggal_kebutuhan' => now()->addDay()->toDateString(),
            'lampiran' => [UploadedFile::fake()->create('lampiran.txt', 16, 'text/plain')],
        ])
        ->assertSessionHasErrors('lampiran.0');
});

test('approved surat can be downloaded by owner', function () {
    Storage::fake('public');
    Storage::disk('public')->put('test.pdf', 'PDF contents');

    $user = createUserWithType('mahasiswa');

    $suratId = DB::table('surats')->insertGetId([
        'jenis_surat_id' => DB::table('jenis_surats')->insertGetId([
            'nama' => 'Surat Keterangan Aktif Kuliah',
            'created_at' => now(),
            'updated_at' => now(),
        ]),
        'pemohon_id' => $user->id,
        'nomor_surat' => 'ASRT-20260001',
        'keperluan' => 'Keperluan administrasi akademik semester ini.',
        'status' => \App\Models\Surat::STATUS_FINISHED,
        'generated_file_path' => 'test.pdf',
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
        'qr_token' => (string) \Illuminate\Support\Str::uuid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('admin.surat.pdf', $suratId))
        ->assertOk();
});
