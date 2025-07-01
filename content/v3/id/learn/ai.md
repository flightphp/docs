# AI & Pengalaman Pengembang dengan Flight

Flight adalah tentang membantu Anda membangun lebih cepat, lebih cerdas, dan dengan lebih sedikit gesekan—terutama saat bekerja dengan alat yang didukung AI dan alur kerja pengembang modern. Halaman ini membahas bagaimana Flight memudahkan Anda untuk meningkatkan proyek Anda dengan AI, serta cara memulai dengan bantuan AI baru yang sudah terintegrasi langsung ke dalam framework dan proyek kerangka.

---

## Siap AI Secara Default: Proyek Kerangka

Proyek starter resmi [flightphp/skeleton](https://github.com/flightphp/skeleton) sekarang dilengkapi dengan instruksi dan konfigurasi untuk asisten pengkodean AI populer:

- **GitHub Copilot**
- **Cursor**
- **Windsurf**

Alat ini telah dikonfigurasi sebelumnya dengan instruksi khusus proyek, sehingga Anda dan tim Anda bisa mendapatkan bantuan yang paling relevan dan sadar konteks saat mengkode. Ini berarti:

- Asisten AI memahami tujuan proyek, gaya, dan persyaratan Anda
- Panduan yang konsisten untuk semua kontributor
- Lebih sedikit waktu yang dihabiskan untuk menjelaskan konteks, lebih banyak waktu untuk membangun

> **Mengapa ini penting?**
>
> Ketika alat AI Anda mengetahui niat dan konvensi proyek Anda, mereka bisa membantu Anda membuat kerangka fitur, merombak kode, dan menghindari kesalahan umum—membuat Anda (dan tim Anda) lebih produktif sejak hari pertama.

---

## Perintah AI Baru di Flight Core

_v3.16.0+_

Flight core sekarang mencakup dua perintah CLI yang kuat untuk membantu Anda mengatur dan mengarahkan proyek dengan AI:

### 1. `ai:init` — Hubungkan ke Penyedia LLM Favorit Anda

Perintah ini memandu Anda melalui pengaturan kredensial untuk penyedia LLM (Large Language Model), seperti OpenAI, Grok, atau Anthropic (Claude).

**Contoh:**
```bash
php runway ai:init
```
Anda akan diminta untuk memilih penyedia Anda, memasukkan kunci API, dan memilih model. Ini memudahkan menghubungkan proyek Anda ke layanan AI terbaru—tanpa konfigurasi manual yang diperlukan.

### 2. `ai:generate-instructions` — Instruksi Pengkodean AI yang Sadar Proyek

Perintah ini membantu Anda membuat atau memperbarui instruksi khusus proyek untuk asisten pengkodean AI. Ini menanyakan beberapa pertanyaan sederhana tentang proyek Anda (seperti apa tujuannya, database apa yang digunakan, ukuran tim, dll.), kemudian menggunakan penyedia LLM Anda untuk menghasilkan instruksi yang disesuaikan.

Jika Anda sudah memiliki instruksi, itu akan memperbarui mereka untuk mencerminkan jawaban yang Anda berikan. Instruksi ini secara otomatis ditulis ke:
- `.github/copilot-instructions.md` (untuk Github Copilot)
- `.cursor/rules/project-overview.mdc` (untuk Cursor)
- `.windsurfrules` (untuk Windsurf)

**Contoh:**
```bash
php runway ai:generate-instructions
```

> **Mengapa ini membantu?**
>
> Dengan instruksi yang terbaru dan khusus proyek, alat AI Anda bisa:
> - Memberikan saran kode yang lebih baik
> - Memahami kebutuhan unik proyek Anda
> - Membantu onboarding kontributor baru lebih cepat
> - Mengurangi gesekan dan kebingungan saat proyek Anda berkembang

---

## Tidak Hanya untuk Membangun Aplikasi AI

Meskipun Anda bisa menggunakan Flight untuk membangun fitur yang didukung AI (seperti chatbot, API pintar, atau integrasi), kekuatan sebenarnya ada pada bagaimana Flight membantu Anda bekerja lebih baik dengan alat AI sebagai pengembang. Ini tentang:

- **Meningkatkan produktivitas** dengan pengkodean yang dibantu AI
- **Menjaga tim Anda tetap selaras** dengan instruksi yang dibagikan dan berkembang
- **Membuat onboarding lebih mudah** untuk kontributor baru
- **Membiarkan Anda fokus pada pembangunan**, bukan berjuang dengan alat Anda

---

## Pelajari Lebih Lanjut & Mulai

- Lihat [Flight Skeleton](https://github.com/flightphp/skeleton) untuk starter yang siap pakai dan ramah AI
- Periksa sisanya dari [Flight documentation](/learn) untuk tips tentang membangun aplikasi PHP yang cepat dan modern