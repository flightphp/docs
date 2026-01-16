# AI & Pengalaman Pengembang dengan Flight

## Gambaran Umum

Flight memudahkan Anda untuk meningkatkan proyek PHP Anda dengan alat berbasis AI dan alur kerja pengembang modern. Dengan perintah bawaan untuk terhubung ke penyedia LLM (Large Language Model) dan menghasilkan instruksi pengkodean AI khusus proyek, Flight membantu Anda dan tim Anda mendapatkan manfaat maksimal dari asisten AI seperti GitHub Copilot, Cursor, Windsurf, dan Antigravity (Gemini).

## Pemahaman

Asisten pengkodean AI paling membantu ketika mereka memahami konteks, konvensi, dan tujuan proyek Anda. Pembantu AI Flight memungkinkan Anda untuk:
- Menghubungkan proyek Anda ke penyedia LLM populer (OpenAI, Grok, Claude, dll.)
- Menghasilkan dan memperbarui instruksi khusus proyek untuk alat AI, sehingga semua orang mendapatkan bantuan yang konsisten dan relevan
- Menjaga tim Anda tetap selaras dan produktif, dengan waktu yang lebih sedikit untuk menjelaskan konteks

Fitur-fitur ini dibangun ke dalam CLI inti Flight dan proyek starter resmi [flightphp/skeleton](https://github.com/flightphp/skeleton).

## Penggunaan Dasar

### Mengatur Kredensial LLM

Perintah `ai:init` akan memandu Anda melalui proses menghubungkan proyek Anda ke penyedia LLM.

```bash
php runway ai:init
```

Anda akan diminta untuk:
- Memilih penyedia Anda (OpenAI, Grok, Claude, dll.)
- Memasukkan kunci API Anda
- Mengatur URL dasar dan nama model

Ini membuat kredensial yang diperlukan agar Anda dapat melakukan permintaan LLM di masa depan.

**Contoh:**
```
Welcome to AI Init!
Which LLM API do you want to use? [1] openai, [2] grok, [3] claude: 1
Enter the base URL for the LLM API [https://api.openai.com]:
Enter your API key for openai: sk-...
Enter the model name you want to use (e.g. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credentials saved to .runway-creds.json
```

### Menghasilkan Instruksi AI Khusus Proyek

Perintah `ai:generate-instructions` membantu Anda membuat atau memperbarui instruksi untuk asisten pengkodean AI, yang disesuaikan dengan proyek Anda.

```bash
php runway ai:generate-instructions
```

Anda akan menjawab beberapa pertanyaan tentang proyek Anda (deskripsi, database, templating, keamanan, ukuran tim, dll.). Flight menggunakan penyedia LLM Anda untuk menghasilkan instruksi, kemudian menulisnya ke:
- `.github/copilot-instructions.md` (untuk GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (untuk Cursor)
- `.windsurfrules` (untuk Windsurf)
- `.gemini/GEMINI.md` (untuk Antigravity)

**Contoh:**
```
Please describe what your project is for? My awesome API
What database are you planning on using? MySQL
What HTML templating engine will you plan on using (if any)? latte
Is security an important element of this project? (y/n) y
...
AI instructions updated successfully.
```

Sekarang, alat AI Anda akan memberikan saran yang lebih pintar dan lebih relevan berdasarkan kebutuhan nyata proyek Anda.

## Penggunaan Lanjutan

- Anda dapat menyesuaikan lokasi file kredensial atau instruksi Anda menggunakan opsi perintah (lihat `--help` untuk setiap perintah).
- Pembantu AI dirancang untuk bekerja dengan penyedia LLM apa pun yang mendukung API yang kompatibel dengan OpenAI.
- Jika Anda ingin memperbarui instruksi Anda seiring berkembangnya proyek, cukup jalankan kembali `ai:generate-instructions` dan jawab prompt lagi.

## Lihat Juga

- [Flight Skeleton](https://github.com/flightphp/skeleton) – Starter resmi dengan integrasi AI
- [Runway CLI](/awesome-plugins/runway) – Lebih lanjut tentang alat CLI yang mendukung perintah ini

## Pemecahan Masalah

- Jika Anda melihat "Missing .runway-creds.json", jalankan `php runway ai:init` terlebih dahulu.
- Pastikan kunci API Anda valid dan memiliki akses ke model yang dipilih.
- Jika instruksi tidak diperbarui, periksa izin file di direktori proyek Anda.

## Changelog

- v3.16.0 – Menambahkan perintah CLI `ai:init` dan `ai:generate-instructions` untuk integrasi AI.