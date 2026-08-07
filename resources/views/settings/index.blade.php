@extends('layouts.app')

@section('title', 'Pengaturan Perusahaan')

@section('content')
<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-8">

            {{-- Informasi Perusahaan --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-building me-2"></i>Informasi Perusahaan</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                               value="{{ old('company_name', $settings['company_name'] ?? '') }}" required>
                        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tagline / Slogan</label>
                        <input type="text" name="company_tagline" class="form-control @error('company_tagline') is-invalid @enderror"
                               value="{{ old('company_tagline', $settings['company_tagline'] ?? '') }}">
                        @error('company_tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NPWP</label>
                        <input type="text" name="company_npwp" class="form-control @error('company_npwp') is-invalid @enderror"
                               value="{{ old('company_npwp', $settings['company_npwp'] ?? '') }}">
                        @error('company_npwp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Kontak --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-phone me-2"></i>Kontak</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="company_email" class="form-control @error('company_email') is-invalid @enderror"
                               value="{{ old('company_email', $settings['company_email'] ?? '') }}">
                        @error('company_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="company_phone" class="form-control @error('company_phone') is-invalid @enderror"
                               value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                        @error('company_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="text" name="company_website" class="form-control @error('company_website') is-invalid @enderror"
                               value="{{ old('company_website', $settings['company_website'] ?? '') }}">
                        @error('company_website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Alamat --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-map-marker-alt me-2"></i>Alamat</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="company_address" class="form-control @error('company_address') is-invalid @enderror" rows="3">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                        @error('company_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kota</label>
                        <input type="text" name="company_city" class="form-control @error('company_city') is-invalid @enderror"
                               value="{{ old('company_city', $settings['company_city'] ?? '') }}">
                        @error('company_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Template Invoice --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-file-invoice me-2"></i>Template Invoice</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Catatan Footer Invoice</label>
                        <input type="text" name="invoice_footer" class="form-control @error('invoice_footer') is-invalid @enderror"
                               value="{{ old('invoice_footer', $settings['invoice_footer'] ?? '') }}">
                        @error('invoice_footer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Syarat &amp; Ketentuan</label>
                        <textarea name="invoice_terms" class="form-control @error('invoice_terms') is-invalid @enderror" rows="3">{{ old('invoice_terms', $settings['invoice_terms'] ?? '') }}</textarea>
                        @error('invoice_terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">

            {{-- Logo Perusahaan --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-image me-2"></i>Logo Perusahaan</div>
                <div class="card-body">
                    @if(!empty($settings['company_logo']))
                        <div class="mb-3 text-center">
                            <img src="{{ asset('storage/' . $settings['company_logo']) }}"
                                 alt="Logo Perusahaan" class="img-fluid" style="max-height:120px; border:1px solid #ddd; padding:4px; border-radius:4px;">
                            <div class="small text-muted mt-1">Logo saat ini</div>
                        </div>
                    @else
                        <div class="mb-3 text-center text-muted py-3" style="border:1px dashed #ccc; border-radius:4px;">
                            <i class="fas fa-image fa-3x mb-2"></i><br>
                            Belum ada logo
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Upload Logo Baru</label>
                        <input type="file" name="company_logo" class="form-control @error('company_logo') is-invalid @enderror"
                               accept="image/png,image/jpg,image/jpeg">
                        <div class="form-text">PNG/JPG, maksimal 2MB.</div>
                        @error('company_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Printer Thermal Bluetooth --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-print me-2"></i>Printer Thermal Bluetooth</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lebar Kertas</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="printer_paper_width" value="58" id="pw58"
                                    {{ ($settings['printer_paper_width'] ?? '80') == '58' ? 'checked' : '' }}>
                                <label class="form-check-label" for="pw58">58 mm (32 karakter)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="printer_paper_width" value="80" id="pw80"
                                    {{ ($settings['printer_paper_width'] ?? '80') == '80' ? 'checked' : '' }}>
                                <label class="form-check-label" for="pw80">80 mm (48 karakter)</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">UUID Layanan BLE <span class="text-muted small">(opsional)</span></label>
                        <input type="text" name="printer_bt_service_uuid" class="form-control form-control-sm font-monospace"
                               placeholder="e7810a71-73ae-499d-8c15-faa9aef0c3f2"
                               value="{{ old('printer_bt_service_uuid', $settings['printer_bt_service_uuid'] ?? '') }}">
                        <div class="form-text">Kosongkan untuk deteksi UUID umum otomatis.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">UUID Karakteristik BLE <span class="text-muted small">(opsional)</span></label>
                        <input type="text" name="printer_bt_char_uuid" class="form-control form-control-sm font-monospace"
                               placeholder="bef8d6c9-9c21-4c9e-b632-bd58c1009f9f"
                               value="{{ old('printer_bt_char_uuid', $settings['printer_bt_char_uuid'] ?? '') }}">
                    </div>
                    <div id="btTestResult" class="small mb-2 text-muted"></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="testBtConnection()">
                        <i class="fas fa-bluetooth me-1"></i>Test Koneksi Printer
                    </button>
                    <div class="alert alert-info py-2 px-3 small mt-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Memerlukan <strong>Chrome / Edge</strong> terbaru dan koneksi <strong>HTTPS</strong> atau <strong>localhost</strong>. Tidak didukung Firefox dan Safari.
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="card mb-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Simpan Pengaturan
                    </button>
                </div>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const BT_COMMON_SERVICES = [
    'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
    '0000ff00-0000-1000-8000-00805f9b34fb',
    '49535343-fe7d-4ae5-8fa9-9fafd205e455',
    '000018f0-0000-1000-8000-00805f9b34fb',
];

async function testBtConnection() {
    const el = document.getElementById('btTestResult');
    if (!navigator.bluetooth) {
        el.className = 'small mb-2 text-danger';
        el.textContent = '✗ Web Bluetooth tidak didukung di browser ini. Gunakan Chrome atau Edge.';
        return;
    }
    const svcUuid = document.querySelector('[name=printer_bt_service_uuid]').value.trim();
    const charUuid = document.querySelector('[name=printer_bt_char_uuid]').value.trim();
    const optionalServices = [...new Set([...BT_COMMON_SERVICES, ...(svcUuid ? [svcUuid] : [])])];

    el.className = 'small mb-2 text-muted';
    el.textContent = 'Mencari printer...';

    try {
        const device = await navigator.bluetooth.requestDevice({ acceptAllDevices: true, optionalServices });
        el.textContent = `Terhubung: ${device.name || 'Printer BT'} — Koneksi berhasil!`;
        el.className = 'small mb-2 text-success fw-semibold';
        device.gatt.disconnect();
    } catch (e) {
        el.className = 'small mb-2 text-' + (e.name === 'NotFoundError' ? 'warning' : 'danger');
        el.textContent = e.name === 'NotFoundError' ? 'Pencarian dibatalkan.' : '✗ ' + e.message;
    }
}
</script>
@endpush
