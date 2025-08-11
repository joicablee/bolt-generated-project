# WpscalerPro: Uçtan Uca Teknik Tasarım ve Geliştirme Rehberi

Bu doküman, WpscalerPro’nun WordPress eklentisi, SaaS API’si ve müşteri paneli için uçtan uca teknik tasarım, entegrasyon, güvenlik, çok dilli altyapı, SEO adaptörleri, streaming, telemetri ve ölçeklenebilirlik konularında doğrudan geliştiriciye yönelik rehber sunar.

---

## 1. Ürün Hedefleri ve Kapsam

- **Eklenti (WpscalerPro):**
  - WooCommerce ürün editörü: AI içerik, başlık, SEO meta, kategori/etiket, paraphrasing, revizyon.
  - Blog üretimi: niş/konu → başlık → outline → makale → görsel → WP’ye ekleme.
  - Görsel araçlar: arka plan beyazlatma, blog görsel üretimi.
  - Toplu işlemler: çoklu ürün/blog optimizasyonu.
  - Çok dilli UI: İngilizce varsayılan, TR/ES ve yeni diller kolay eklenebilir.

- **SaaS Backend (API):**
  - Lisans/paket doğrulama, domain bağlama, kota takibi.
  - Prompt yönetimi, model yönlendirme (OpenAI, Anthropic).
  - İçerik/görsel servisleri, post-processing, moderasyon.
  - Job queue, streaming/SSE, webhooks, log/metric.

- **Web Sitesi (SaaS Satış + Panel):**
  - Paket/abonelik satışı, ödeme (iyzico, Stripe).
  - Lisans yönetimi, kullanım raporları, arşiv.
  - Çok dilli UI, çoklu para birimi.

---

## 2. Çok Dilli (Multilang) Strateji

- **Kapsam:** Eklenti UI, web sitesi, e-posta şablonları, API mesajları.
- **Dosya tabanlı çeviri:**
  - Eklenti: `languages/{locale}.json` + `.pot` (PHP için gettext, JS için JSON).
  - Website: `/locales/{locale}/common.json`, `/emails/{locale}/*.mjml`.
  - API: `/i18n/{locale}.json`, `/prompts/{kind}/{locale}.tmpl`.
- **Dil ekleme:** Locale kodu ekle, ilgili dosyaları kopyala/çevir, konfigürasyona tanıt.
- **Fallback:** requested → en.
- **RTL desteği:** UI’da `dir="rtl"` koşullu.
- **Trick:** Build sırasında eksik çeviriler için CLI “missing keys detector”.

---

## 3. WordPress Eklentisi Tasarım

### 3.1 Dizin Yapısı

```
wpscalerpro/
  wpscalerpro.php
  inc/
    Admin/
    Api/
    Services/
    Views/
  assets/
    js/ (React admin SPA - Vite)
    css/
  languages/
    en.json, tr.json, es.json, wpscalerpro.pot
  readme.txt
```

- **Build:** Vite + React (admin), PHP i18n: `load_plugin_textdomain`, JS i18n: JSON yükleme.

### 3.2 Güvenlik ve İzin

- Capabilities: `edit_products`, `manage_woocommerce`, `edit_posts`.
- Nonce + WP REST: `check_ajax_referer`, `current_user_can`.
- Domain fingerprint: `site_url + salt` ile HMAC.
- Sanitization: `sanitize_text_field`, `wp_kses_post`, `esc_html`, `wp_kses`.
- **Trick:** Admin-ajax yerine WP REST, nonce + capability ile koruma.

### 3.3 WooCommerce Ürün Editörü Entegrasyonu

- Metabox/sekme: brief, hedef dil, ton, anahtar kelimeler, aksiyon butonları.
- Otomatik doldurma: `post_content`, `post_excerpt`, `post_title`, SEO meta (adapter ile).
- Toplu işlemler: Bulk action, paralel istek sınırı (3–5).

### 3.4 Blog İçerik Ekranı

- Akış: niş/konu → başlık → outline → içerik (SSE ile canlı) → görsel → taslak.
- Özellikler: otomatik öne çıkan görsel, alt metin, içerik uzunluğu, ton, CTA, tablo/TOC.
- **Trick:** SSE fallback polling, “cancel” butonu ile job iptal.

### 3.5 Görsel Araçlar

- Arka plan beyazlatma: görsel URL → SaaS → fal.ai → S3 → WP.
- JPEG kalite 82–85, WebP seçeneği, orijinali koru, yeni dosya “-bgwhite”.
- Blog görsel üretimi: prompt, stil, renk, yönlendirme, otomatik boyutlandırma.

### 3.6 SEO Eklenti Adaptörleri

```php
interface WPSP_SEO_Adapter {
  public function is_active(): bool;
  public function set_title(int $post_id, string $title): void;
  public function set_description(int $post_id, string $desc): void;
  public function set_focus_keywords(int $post_id, array $keywords): void;
  public function set_social(int $post_id, array $og, array $twitter): void;
}
```

- **Yoast SEO:** `_yoast_wpseo_title`, `_yoast_wpseo_metadesc`, focus keyword alanı sürüme göre değişir.
- **RankMath:** `rank_math_title`, `rank_math_description`, `rank_math_focus_keyword`.
- **SEOPress:** `_seopress_titles_title`, `_seopress_titles_desc`, `_seopress_analysis_target_kw`.
- **Trick:** Adapter’larda “feature detection”, aktif alanları kullanıcıya göster.

### 3.7 WP REST Endpoints

Prefix: `/wp-json/wpscalerpro/v1`

- POST `/license/activate`
- POST `/content/generate`
- POST `/content/optimize/title`
- POST `/content/optimize/seo`
- POST `/content/paraphrase`
- POST `/content/revise`
- POST `/taxonomy/suggest`
- POST `/media/bg-remove`
- POST `/media/generate`
- GET `/jobs/{jobId}`

Tümü nonce + capability kontrolü sonrası SaaS API’ye proxy eder.

### 3.8 Eklenti Tarafında Örnekler

**Lisans aktivasyon:**
```php
$response = wp_remote_post( $api_url . '/v1/licenses/activate', [
  'headers' => [ 'Content-Type' => 'application/json' ],
  'body'    => wp_json_encode([
    'license_key' => $license_key,
    'domain_fingerprint' => WPSP_Security::fingerprint(),
    'site_url' => get_site_url(),
    'locale' => WPSP_I18n::get_locale_for_user(),
  ]),
  'timeout' => 20,
]);
```

**LLM çıktısını güvenli kaydetme:**
```php
$clean_html = wp_kses_post( $llm_html );
wp_update_post([
  'ID' => $post_id,
  'post_content' => $clean_html,
]);
```

**React (SSE dinleme):**
```js
const evt = new EventSource(`${base}/stream?jobId=${id}`);
evt.onmessage = e => setText(prev => prev + e.data);
evt.onerror = () => evt.close();
```

---

## 4. SaaS Backend (API) Tasarım

### 4.1 Teknoloji ve Altyapı

- API: Node.js (NestJS/Express) veya Python (FastAPI)
- DB: PostgreSQL (jsonb), Redis (cache + queue)
- Kuyruk: BullMQ veya Celery
- Storage: S3 uyumlu (AWS S3 / MinIO)
- Observability: merkezi log (JSON), tracing (OpenTelemetry), metrics (Prometheus)
- API dokümantasyon: OpenAPI/Swagger
- Stateless ölçekleme: JWT, sticky session gerekmez

### 4.2 Veri Modeli (SQL Özet)

```sql
create table tenants (...);
create table licenses (...);
create table usage_events (...);
create table prompts (...);
create table requests (...);
create table images (...);
```
(Tam şema için üstteki ana dokümana bakınız.)

### 4.3 API Endpoint’leri

- **Auth/Lisans**
  - POST `/v1/licenses/activate`
  - GET `/v1/licenses/me`
- **İçerik**
  - POST `/v1/content/generate`
  - POST `/v1/content/optimize/title`
  - POST `/v1/content/optimize/seo`
  - POST `/v1/content/paraphrase`
  - POST `/v1/content/revise`
- **Taksonomi**
  - POST `/v1/taxonomy/suggest`
- **Görsel (async jobs)**
  - POST `/v1/media/bg-remove`
  - POST `/v1/media/generate`
  - GET `/v1/jobs/{jobId}`
- **Yönetim**
  - GET `/v1/prompts/{key}`
  - GET `/v1/usage/summary`
  - Webhooks: içerik/görsel işi tamamlandığında POST (HMAC imzalı)

### 4.4 Prompt Yönetimi ve Post-Processing

- Templating: `{{locale}}`, `{{tone}}`, `{{brand_voice}}`, `{{keywords}}`, `{{length}}`
- Versiyonlama: prompts.key + version + locale
- Post-processing: başlık uzunluğu, meta description, anahtar kelime yoğunluğu, dilbilgisi ve HTML doğrulama
- **Trick:** Hash tabanlı cache (payload hash’i) ile tekrar maliyetini düşür

### 4.5 Model Katmanı ve Maliyet

- Plan → model haritası: Basic/Pro
- Uzun metinlerde böl-parça (“sectional generation”)
- Retry strategy: 5xx/backoff, model düşürme

### 4.6 Kuyruk ve Streaming

- Job queue: contentQueue, imageQueue
- Streaming: SSE ile token bazlı akış, fallback polling
- İptal: job cancel endpoint, kotaları iade et

### 4.7 Güvenlik

- Auth: JWT (HMAC), scope’lar
- CORS: lisanslı domain whitelisti, origin + token doğrulama
- Rate limiting: Redis leaky bucket (per-license, per-IP)
- HMAC imzalı webhooks
- PII korunumu: payload minimizasyon, log’larda maskeleme
- Prompt injection mitigasyonu: sistem prompt sabit, user input ayrı role

---

## 5. Web Sitesi (SaaS Satış + Panel)

### 5.1 Teknoloji ve i18n

- Next.js/React + Tailwind
- i18n: next-intl/next-i18next, `/[locale]/...` route
- Çoklu para birimi: fiyatlandırma katmanında kur tablosu
- E-postalar: MJML şablonları

### 5.2 Özellikler ve Akışlar

- Satış: paket/abonelik, ödeme (iyzico, Stripe), webhook doğrulama
- Müşteri paneli: lisans anahtarları, kullanım grafikleri, içerik arşivi, API ayarları (şifreli saklama)
- Ajans modu: çoklu domain, alt kullanıcı, white-label
- **Trick:** KDV/Vergi alanları çok dilli validasyon, deneme modu

---

## 6. Kota, Paket, Lisans ve Faturalama

- Kota modeli: content_generate, seo_optimize, paraphrase, revision, image_ops
- Düşüm stratejisi: başarılı yanıt → düş, hata/iptal → düşme
- Yıllık plan (kendi API anahtarı): kota sınırsız, anahtar AES-256 ile şifreli
- Faturalama: abonelik yenileme, ek paket, otomatik e-posta bildirimleri

---

## 7. Performans ve Ölçeklenebilirlik

- HTTP: Keep-Alive, HTTP/2, gzip/br, Idempotency-Key, Correlation-Id
- Cache: prompt+brief+params hash cache (Redis), CDN ile statik görsel
- DB: yazma/okuma ayrımı, JSONB index’leri
- UI: debounce, paralel istek sınırı
- **Trick:** “Cold start” için küçük ısınma job’ları

---

## 8. Geliştirme, Test, CI/CD

- Repo ayrımı: plugin, api, web
- CI: lint/format, unit+integration, SAST, dependency scan
- CD: plugin .zip build, API container image, web Vercel/Netlify
- Test: mock LLM/görsel servis, E2E, load test
- **Trick:** “Golden output” snapshot testleri

---

## 9. Telemetri, Loglama, Gözlemlenebilirlik

- Log formatı: JSON; timestamp, level, correlationId, licenseId, endpoint, duration, error_code
- Metrics: istek sayısı, hata oranı, cevap süresi, token tüketimi, job queue uzunluğu
- Tracing: request span → LLM call → post-processing
- Gizlilik: PII maskeleme, “telemetry off” seçeneği, KVKK/GDPR uyumu

---

## 10. Güvenlik ve Uyum

- WP: nonce+capability, check_admin_referer, wp_kses_post, permission_callback
- API: JWT expiry kısa, rate limit, webhook HMAC, secrets manager
- **Trick:** Domain binding strict/lenient, staging alan whitelist

---

## 11. UI/UX Detayları ve Metin Stratejisi

- Başlık optimizasyonu: 3–5 aday, skor, gerekçe, onay diyaloğu
- Revizyon raporu: okunabilirlik, anahtar kelime yoğunluğu, CTA önerisi, “sadece önerileri uygula” toggle
- Görsel iş akışı: iş kuyruğu kartı, ETA, sonucu indir/uygula
- Çok dilli UX: dil seçimi, eslint-plugin-i18n, build-time “missing key” raporu

---

## 12. Geliştirme Trick’leri

- Hash cache ile yinelenen brief maliyetini düşür
- SSE + “resume after disconnect” için son token offset’ini sakla
- “Safe-apply” yazma: SEO meta yazmadan önce eklenti versiyonunu tespit et
- Prompt injection’a karşı: sistem prompt sabit, user input escape
- LLM post-processing ile karakter/kelime sınırlarını normalize et
- Plan değişimlerinde kota taşıma: kalan bakiyeyi yeni plana göç ettir

---

## 13. Örnek Akışlar (JSON)

**Ürün açıklaması üret:**
```json
POST /v1/content/generate
{
  "type": "product",
  "locale": "tr",
  "tone": "resmi",
  "brief": "Pamuklu erkek tişört, nefes alan kumaş, yaz koleksiyonu",
  "target_keywords": ["erkek tişört", "pamuklu", "yazlık"],
  "length": 180
}
```
Yanıt:
```json
{
  "text": "<p>…HTML içerik…</p>",
  "seo": {
    "title": "Pamuklu Erkek Tişört - Yaz Koleksiyonu",
    "description": "Nefes alan pamuklu erkek tişört…",
    "keywords": ["erkek tişört","pamuklu","yazlık"]
  },
  "diagnostics": {"readability": 72, "kw_density": 1.5}
}
```

**Başlık optimizasyonu:**
```json
POST /v1/content/optimize/title
{
  "current_title": "Erkek Tişört",
  "locale": "tr",
  "target_keywords": ["erkek tişört","pamuklu"]
}
```
Yanıt:
```json
{
  "candidates": [
    {"title":"Pamuklu Erkek Tişört | Nefes Alan Kumaş","score":92,"length":44,"rationale":"KW + fayda"},
    {"title":"Yazlık Pamuklu Erkek Tişört - Rahat Kesim","score":88,"length":46,"rationale":"Mevsimsel niş"}
  ]
}
```

**Görsel üretimi job:**
```json
POST /v1/media/generate
{
  "prompt": "Minimalist flat-lay cotton t-shirt on white background",
  "style": "studio",
  "aspect": "1:1"
}
```
Yanıt:
```json
{
  "jobId": "img_01HD..."
}
```

---

## 14. Dil Ekleme Kılavuzu

1. Locale kodu belirle (ör: fr)
2. Eklenti: `languages/en.json` dosyasını `fr.json` olarak kopyala ve çevir
3. Website: `/locales/fr/common.json` ve e-posta şablonlarını oluştur
4. API: `/i18n/fr.json`, `/prompts/*/fr.tmpl`, SUPPORTED_LOCALES listesine ekle
5. Build aşamasında “missing keys” kontrolü
6. RTL gerekiyorsa (ar): UI `dir="rtl"` otomatik uygula

---

**Not:** Tüm akışlarda güvenlik, performans ve çok dilli destek önceliklidir. Her bölümdeki “trick” ipuçları, pratikte karşılaşılacak sorunlara hızlı çözüm sağlar.
