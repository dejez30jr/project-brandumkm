<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @method static Builder|static query()
 * @method static static create(array $attributes = [])
 * @mixin Builder
 * @property int $id
 * @property int $umkm_id
 * @property string $file_path
 * @property string|null $keterangan
 * @property int $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Umkm $umkm
 * @property-read \App\Models\User $uploadedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AfterBranding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AfterBranding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AfterBranding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AfterBranding whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AfterBranding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AfterBranding whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AfterBranding whereUmkmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AfterBranding whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AfterBranding whereUploadedBy($value)
 */
	class AfterBranding extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static Builder|static query()
 * @method static Builder|static where(string $column, mixed $operator = null, mixed $value = null)
 * @method static static create(array $attributes = [])
 * @method static static updateOrCreate(array $attributes, array $values = [])
 * @method static static firstOrCreate(array $attributes, array $values = [])
 * @method static static find(int $id)
 * @method static static first()
 * @mixin Builder
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Umkm> $umkms
 * @property-read int|null $umkms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kota newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kota newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kota whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kota whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kota whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kota whereUpdatedAt($value)
 */
	class Kota extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static Builder|static query()
 * @method static Builder|static where(string $column, mixed $operator = null, mixed $value = null)
 * @method static static create(array $attributes = [])
 * @mixin Builder
 * @property int $id
 * @property int $user_id
 * @property string $judul
 * @property string $pesan
 * @property string $tipe
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $notifiable
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi whereJudul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi whereNotifiableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi whereNotifiableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi wherePesan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi whereTipe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notifikasi whereUserId($value)
 */
	class Notifikasi extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static Builder|static query()
 * @method static Builder|static where(string $column, mixed $operator = null, mixed $value = null)
 * @method static Builder|static whereIn(string $column, array $values)
 * @method static Builder|static whereNotNull(string $column)
 * @method static Builder|static whereNull(string $column)
 * @method static Builder|static whereHas(string $relation, \Closure $callback = null)
 * @method static Builder|static selectRaw(string $expression, array $bindings = [])
 * @method static Builder|static leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static static create(array $attributes = [])
 * @method static static updateOrCreate(array $attributes, array $values = [])
 * @method static static find(int $id)
 * @mixin Builder
 * @property int $id
 * @property string $nama_pemilik
 * @property string $nama_usaha
 * @property string $alamat_usaha
 * @property string $no_wa
 * @property string|null $radius
 * @property string|null $no_rekening
 * @property string|null $nama_bank
 * @property string|null $atas_nama_rekening
 * @property string|null $jam_buka
 * @property string|null $jam_tutup
 * @property string|null $request_text
 * @property string|null $catatan
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $sharelock_url
 * @property numeric|null $depan_atas_w
 * @property numeric|null $depan_atas_h
 * @property numeric|null $depan_tengah_w
 * @property numeric|null $depan_tengah_h
 * @property numeric|null $depan_bawah_w
 * @property numeric|null $depan_bawah_h
 * @property numeric|null $kanan_atas_w
 * @property numeric|null $kanan_atas_h
 * @property numeric|null $kanan_tengah_w
 * @property numeric|null $kanan_tengah_h
 * @property numeric|null $kanan_bawah_w
 * @property numeric|null $kanan_bawah_h
 * @property numeric|null $kiri_atas_w
 * @property numeric|null $kiri_atas_h
 * @property numeric|null $kiri_tengah_w
 * @property numeric|null $kiri_tengah_h
 * @property numeric|null $kiri_bawah_w
 * @property numeric|null $kiri_bawah_h
 * @property numeric|null $depan_panel_atas_m2
 * @property numeric|null $depan_panel_tengah_m2
 * @property numeric|null $depan_panel_bawah_m2
 * @property numeric|null $kanan_panel_atas_m2
 * @property numeric|null $kanan_panel_tengah_m2
 * @property numeric|null $kanan_panel_bawah_m2
 * @property numeric|null $kiri_panel_atas_m2
 * @property numeric|null $kiri_panel_tengah_m2
 * @property numeric|null $kiri_panel_bawah_m2
 * @property numeric|null $total_area_branding
 * @property bool $memenuhi_kriteria
 * @property string $status
 * @property string|null $alasan_reject
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $approved_by
 * @property string|null $foto_depan
 * @property string|null $foto_kanan
 * @property string|null $foto_kiri
 * @property string|null $foto_plang_alfamart
 * @property string|null $foto_tampak_jauh
 * @property string|null $video_validasi
 * @property string|null $design_final
 * @property string|null $design_gerobak_depan
 * @property string|null $design_gerobak_kiri
 * @property string|null $design_gerobak_kanan
 * @property int $kota_id
 * @property int $submitted_by
 * @property string|null $stiker_tampak_depan
 * @property string|null $stiker_tampak_kanan
 * @property string|null $stiker_tampak_kiri
 * @property string|null $foto_wide
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AfterBranding> $afterBrandings
 * @property-read int|null $after_brandings_count
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UmkmDesign> $designs
 * @property-read int|null $designs_count
 * @property-read \App\Models\Kota $kota
 * @property-read \App\Models\User $submittedBy
 * @property-read \App\Models\UmkmDesign|null $umkmDesign
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereAlamatUsaha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereAlasanReject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereAtasNamaRekening($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDepanAtasH($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDepanAtasW($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDepanBawahH($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDepanBawahW($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDepanPanelAtasM2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDepanPanelBawahM2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDepanPanelTengahM2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDepanTengahH($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDepanTengahW($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDesignFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDesignGerobakDepan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDesignGerobakKanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereDesignGerobakKiri($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereFotoDepan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereFotoKanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereFotoKiri($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereFotoPlangAlfamart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereFotoTampakJauh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereFotoWide($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereJamBuka($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereJamTutup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKananAtasH($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKananAtasW($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKananBawahH($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKananBawahW($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKananPanelAtasM2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKananPanelBawahM2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKananPanelTengahM2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKananTengahH($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKananTengahW($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKiriAtasH($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKiriAtasW($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKiriBawahH($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKiriBawahW($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKiriPanelAtasM2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKiriPanelBawahM2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKiriPanelTengahM2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKiriTengahH($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKiriTengahW($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereKotaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereMemenuhiKriteria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereNamaBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereNamaPemilik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereNamaUsaha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereNoRekening($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereNoWa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereRequestText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereSharelockUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereStikerTampakDepan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereStikerTampakKanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereStikerTampakKiri($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereSubmittedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereTotalAreaBranding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Umkm whereVideoValidasi($value)
 */
	class Umkm extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static Builder|static query()
 * @method static Builder|static where(string $column, mixed $operator = null, mixed $value = null)
 * @method static Builder|static whereIn(string $column, array $values)
 * @method static Builder|static selectRaw(string $expression, array $bindings = [])
 * @method static static create(array $attributes = [])
 * @method static static find(int $id)
 * @mixin Builder
 * @property int $id
 * @property int $umkm_id
 * @property int $designer_id
 * @property string $file_path
 * @property string|null $gerobak_depan
 * @property string|null $gerobak_kiri
 * @property string|null $gerobak_kanan
 * @property string $status
 * @property string|null $catatan_revisi
 * @property int $versi
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $designer
 * @property-read \App\Models\Umkm $umkm
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereCatatanRevisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereDesignerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereGerobakDepan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereGerobakKanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereGerobakKiri($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereUmkmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UmkmDesign whereVersi($value)
 */
	class UmkmDesign extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static Builder|static query()
 * @method static Builder|static where(string $column, mixed $operator = null, mixed $value = null)
 * @method static Builder|static whereIn(string $column, array $values)
 * @method static static create(array $attributes = [])
 * @method static static updateOrCreate(array $attributes, array $values = [])
 * @method static static firstOrCreate(array $attributes, array $values = [])
 * @mixin Builder
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property int|null $kota_id
 * @property bool $is_active
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UmkmDesign> $designs
 * @property-read int|null $designs_count
 * @property-read \App\Models\Kota|null $kota
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notifikasi> $notifikasis
 * @property-read int|null $notifikasis_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notifikasi> $readNotifications
 * @property-read int|null $read_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Umkm> $submittedUmkms
 * @property-read int|null $submitted_umkms_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereKotaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}

