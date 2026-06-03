/**
 * Cloudflare Worker untuk men-trigger Laravel Queue via HTTP Request.
 * 
 * Cara Penggunaan:
 * 1. Buka dashboard Cloudflare -> Workers & Pages -> Create Worker.
 * 2. Ganti kode bawaannya dengan kode di bawah ini.
 * 3. Ubah bagian `TARGET_URL` dengan domain aplikasi Anda yang sebenarnya.
 * 4. Klik "Deploy".
 * 5. Buka tab "Triggers" -> Tambahkan "Cron Trigger" -> Setel setiap 1 menit ( * * * * * ).
 */

export default {
  async fetch(request, env, ctx) {
    // Digunakan untuk testing/ping manual jika worker diakses langsung lewat browser
    return await this.triggerQueue();
  },

  async scheduled(event, env, ctx) {
    // Dijalankan otomatis oleh Cron Trigger dari Cloudflare (Setiap 1 Menit)
    ctx.waitUntil(this.triggerQueue());
  },

  async triggerQueue() {
    // GANTI URL DI BAWAH INI SESUAI DOMAIN SERVER ANDA
    // Pastikan key-nya sama dengan yang ada di routes/web.php
    const TARGET_URL = "https://domain-vidflow-anda.com/system/run-worker?key=vidflow_secret_123";

    try {
      const response = await fetch(TARGET_URL, {
        method: "GET",
        headers: {
          "User-Agent": "Cloudflare-Queue-Worker",
          "Accept": "application/json"
        }
      });

      if (!response.ok) {
        return new Response("Gagal menjalankan worker, HTTP Status: " + response.status, { status: response.status });
      }

      const result = await response.json();
      return new Response(JSON.stringify(result, null, 2), {
        headers: { "Content-Type": "application/json" }
      });
      
    } catch (error) {
      return new Response("Koneksi Error: " + error.message, { status: 500 });
    }
  }
};
