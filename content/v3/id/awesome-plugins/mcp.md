# Server MCP FlightPHP

Server MCP FlightPHP memberikan akses instan dan terstruktur kepada asisten pengkodean AI yang kompatibel dengan MCP ke seluruh dokumentasi FlightPHP — routing, middleware, plugin, panduan, dan lainnya. Alih-alih AI Anda menghasilkan detail API secara halusinasi atau menebak tanda tangan metode, ia mengambil dokumen asli sesuai permintaan. Tidak ada kunci API, tidak diperlukan instalasi untuk versi yang dihosting.

Kunjungi [repositori Github](https://github.com/flightphp/mcp) untuk kode sumber lengkap dan detail.

## Mulai Cepat

Server ini dihosting secara publik dan siap digunakan:

```
https://mcp.flightphp.com/mcp
```

Cukup tambahkan URL tersebut ke ekstensi pengkodean AI Anda. Tidak ada pendaftaran, tidak ada kredensial. Lihat bagian [Konfigurasi IDE](#ide--ai-extension-configuration) di bawah untuk konfigurasi copy-paste untuk alat paling populer.

## Apa yang Dilakukannya

Setelah terhubung, asisten AI Anda dapat:

- **Telusuri semua dokumen yang tersedia** — daftarkan setiap topik inti, panduan, dan halaman plugin
- **Ambil halaman dokumentasi apa pun** — ambil konten lengkap untuk routing, middleware, permintaan, keamanan, dan lainnya
- **Cari dokumen plugin** — dapatkan dokumentasi lengkap untuk ActiveRecord, Session, Tracy, Runway, dan semua plugin resmi lainnya
- **Ikuti panduan langkah demi langkah** — akses walkthrough lengkap untuk membangun blog, REST API, dan aplikasi yang diuji
- **Cari di seluruhnya** — temukan halaman relevan di seluruh dokumen inti, panduan, dan plugin sekaligus

### Poin Kunci
- **Pengaturan nol** — server yang dihosting di `https://mcp.flightphp.com/mcp` tidak memerlukan instalasi atau kunci API.
- **Selalu terkini** — server mengambil dokumen secara langsung dari [docs.flightphp.com](https://docs.flightphp.com), sehingga selalu terbaru.
- **Bekerja di mana saja** — alat apa pun yang mendukung transport HTTP Streamable MCP dapat terhubung.
- **Dapat dihosting sendiri** — jalankan instance Anda sendiri dengan PHP >= 8.1 dan Composer jika Anda lebih suka.

## Konfigurasi IDE / Ekstensi AI

Server menggunakan transport HTTP Streamable. Pilih ekstensi Anda di bawah dan tempelkan konfigurasi.

### Claude Code (CLI)

Jalankan perintah berikut untuk menambahkannya ke proyek Anda:

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

Atau tambahkan secara manual ke `.mcp.json` proyek Anda:

```json
{
  "mcpServers": {
    "flightphp-docs": {
      "type": "http",
      "url": "https://mcp.flightphp.com/mcp"
    }
  }
}
```

### GitHub Copilot (VS Code)

Tambahkan ke `.vscode/mcp.json` di workspace Anda:

```json
{
  "servers": {
    "flightphp-docs": {
      "type": "http",
      "url": "https://mcp.flightphp.com/mcp"
    }
  }
}
```

### Kilo Code (VS Code)

Tambahkan ke `settings.json` VS Code Anda:

```json
{
  "kilocode.mcpServers": {
    "flightphp-docs": {
      "url": "https://mcp.flightphp.com/mcp",
      "transport": "streamable-http"
    }
  }
}
```

### Continue.dev (VS Code / JetBrains)

Tambahkan ke `~/.continue/config.json`:

```json
{
  "mcpServers": [
    {
      "name": "flightphp-docs",
      "transport": {
        "type": "http",
        "url": "https://mcp.flightphp.com/mcp"
      }
    }
  ]
}
```

## Alat yang Tersedia

Server MCP mengekspos alat berikut ke asisten AI Anda:

| Alat | Deskripsi |
|------|-------------|
| `list_docs_pages` | Daftarkan semua topik dokumentasi inti yang tersedia dengan slug dan deskripsi |
| `get_docs_page` | Ambil halaman dokumen inti berdasarkan slug topik (mis. `routing`, `middleware`, `security`) |
| `list_guide_pages` | Daftarkan semua panduan langkah demi langkah yang tersedia |
| `get_guide_page` | Ambil panduan lengkap berdasarkan slug (mis. `blog`, `unit-testing`) |
| `list_plugin_pages` | Daftarkan semua halaman plugin dan ekstensi yang tersedia |
| `get_plugin_docs` | Ambil dokumentasi plugin lengkap berdasarkan slug (mis. `active-record`, `session`, `jwt`) |
| `search_docs` | Cari di seluruh dokumen, panduan, dan plugin untuk kata kunci atau topik |
| `fetch_url` | Ambil halaman apa pun secara langsung berdasarkan URL lengkap `docs.flightphp.com` |

## Self-Hosting

Lebih suka menjalankan instance Anda sendiri? Anda memerlukan PHP >= 8.1 dan Composer.

```bash
git clone https://github.com/flightphp/mcp.git
cd mcp
composer install
php server.php
```

Server dimulai di `http://0.0.0.0:8890/mcp` secara default. Perbarui konfigurasi IDE Anda untuk mengarah ke alamat lokal Anda:

```json
{
  "mcpServers": {
    "flightphp-docs": {
      "type": "http",
      "url": "http://localhost:8890/mcp"
    }
  }
}
```