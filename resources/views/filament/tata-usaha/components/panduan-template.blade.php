<div class="space-y-4 text-sm">
    <div>
        <p class="mb-2">Template ini digunakan untuk mengimpor data siswa secara massal. Ikuti panduan berikut:</p>
    </div>

    <table class="w-full border-collapse border">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2 text-left font-medium">Kolom</th>
                <th class="border p-2 text-left font-medium">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border p-2 font-medium">Nama</td>
                <td class="border p-2">Nama lengkap siswa (wajib diisi).</td>
            </tr>
            <tr>
                <td class="border p-2 font-medium">NISN</td>
                <td class="border p-2">Nomor Induk Siswa Nasional (opsional).</td>
            </tr>
            <tr>
                <td class="border p-2 font-medium">NIS</td>
                <td class="border p-2">Nomor Induk Sekolah (opsional). Harus unik — tidak boleh sama dengan NIS siswa lain.</td>
            </tr>
            <tr>
                <td class="border p-2 font-medium">Gender</td>
                <td class="border p-2">Isi dengan <strong>Laki-laki</strong>, <strong>Perempuan</strong>, <strong>male</strong>, atau <strong>female</strong>.</td>
            </tr>
            <tr>
                <td class="border p-2 font-medium">Tempat Lahir</td>
                <td class="border p-2">Kota tempat lahir siswa (opsional).</td>
            </tr>
            <tr>
                <td class="border p-2 font-medium">Tanggal Lahir</td>
                <td class="border p-2">Format <strong>YYYYMMDD</strong> (contoh: <code>20050115</code> untuk 15 Januari 2005).</td>
            </tr>
            <tr>
                <td class="border p-2 font-medium">Alamat</td>
                <td class="border p-2">Alamat lengkap siswa (opsional).</td>
            </tr>
            <tr>
                <td class="border p-2 font-medium">Telepon</td>
                <td class="border p-2">Nomor telepon yang bisa dihubungi (opsional).</td>
            </tr>
            <tr>
                <td class="border p-2 font-medium">Email</td>
                <td class="border p-2">Email siswa (opsional). Digunakan untuk akun login. Harus unik — tidak boleh sama dengan email yang sudah terdaftar.</td>
            </tr>
        </tbody>
    </table>

    <div class="bg-blue-50 border border-blue-200 rounded p-3 text-blue-800">
        <p class="font-medium mb-1">Catatan:</p>
        <ul class="list-disc list-inside space-y-1">
            <li>Baris pertama file adalah header — jangan dihapus atau diubah.</li>
            <li>Baris kedua adalah contoh — bisa dihapus sebelum diisi data sebenarnya.</li>
            <li>Data yang sudah diimpor tidak bisa diimpor ulang (NIS/Email duplikat akan dilewati).</li>
            <li>Saat impor, sistem otomatis membuat akun login untuk setiap siswa dengan password default.</li>
        </ul>
    </div>
</div>
