<?php

namespace App\Modules\Pagi\Services;

use App\Models\Portal\PortalSetting;

class ContentModerationService
{
    /**
     * Peta leetspeak (konversi angka/simbol ke huruf)
     */
    private const LEET_MAP = [
        '0' => 'o',
        '1' => 'i',
        '3' => 'e',
        '4' => 'a',
        '5' => 's',
        '7' => 't',
        '8' => 'b',
        '@' => 'a',
        '$' => 's',
        '!' => 'i',
        '+' => 't',
    ];

    /**
     * Kamus Kata Terlarang bawaan (Berdasarkan Kategori)
     */
    private const DEFAULT_DICTIONARY = [
        'judi_online' => [
            'slot', 'slot88', 'gacor', 'maxwin', 'pragmatic', 'zeus', 'rtp',
            'depo', 'wd', 'scatter', 'judol', 'judi', 'judionline', 'pragmaticplay',
        ],
        'profanity' => [
            'anjing', 'babi', 'bangsat', 'kontol', 'memek', 'ngentot', 'jancok',
            'asu', 'dancok', 'taik', 'tai', 'tolol', 'goblok', 'bajingan',
            'kampang', 'itil', 'pantek', 'peler', 'biadab', 'pepek', 'kimak',
        ],
        'harassment' => [
            'tolol banget', 'goblok banget', 'jelek banget', 'cacat', 'autis',
            'banci', 'bencong', 'dasar lu', 'matamu', 'dasar lu anjing',
        ],
        'sexual' => [
            'bokep', 'sange', 'vcs', 'porno', 'nude', 'telanjang', 'mesum',
            'openbo', 'bo_real', 'vid_viral_mesum',
        ],
        'threat' => [
            'tak pateni', 'tak hajar', 'gua bunuh', 'gua sikat', 'mati aja',
            'mati lu', 'pengen tak bunuh',
        ],
        'phishing' => [
            'spambot', 'hack_account', 'klaim_saldo', 'dana_kaget_palsu',
        ],
    ];

    /**
     * Menormalisasi teks: mengubah leetspeak ke huruf normal & menghilangkan karakter pemisah sembarangan.
     */
    public function normalizeText(string $text): string
    {
        $normalized = strtolower($text);

        // Subsitusi leetspeak (misal: s10t -> slot, 4nj1ng -> anjing)
        $normalized = strtr($normalized, self::LEET_MAP);

        // Hapus karakter khusus di tengah kata yang digunakan untuk menyamarkan (misal: a.n.j.i.n.g -> anjing)
        $normalized = preg_replace('/(?<=\w)[\.\-_\\*](?=\w)/u', '', $normalized);

        // Hapus spasi berlebih
        return preg_replace('/\s+/u', ' ', $normalized);
    }

    /**
     * Mendapatkan seluruh daftar kata terlarang (Bawaan + Pengaturan Custom Admin dari DB)
     */
    public function getBannedWords(): array
    {
        $customWordsJson = PortalSetting::query()->where('key', 'pagi_banned_words')->value('value');
        $customWords = [];

        if ($customWordsJson) {
            $decoded = json_decode($customWordsJson, true);
            if (is_array($decoded)) {
                $customWords = array_map('strtolower', array_map('trim', $decoded));
            }
        }

        $allWords = [];
        foreach (self::DEFAULT_DICTIONARY as $cat => $words) {
            foreach ($words as $w) {
                $allWords[$w] = $cat;
            }
        }

        foreach ($customWords as $cw) {
            if (! empty($cw)) {
                $allWords[$cw] = 'custom_admin';
            }
        }

        return $allWords;
    }

    /**
     * Memindai teks dan mengembalikan hasil analisis tingkat risiko
     */
    public function scan(string $text): array
    {
        $enableLocalEngine = filter_var(PortalSetting::query()->where('key', 'pagi_enable_local_engine')->value('value') ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $enableGoogleAi = filter_var(PortalSetting::query()->where('key', 'pagi_enable_google_ai')->value('value') ?? 'false', FILTER_VALIDATE_BOOLEAN);

        $normalized = $this->normalizeText($text);

        $matchedWords = [];
        $matchedCategories = [];

        if ($enableLocalEngine) {
            $dictionary = $this->getBannedWords();
            foreach ($dictionary as $word => $category) {
                $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
                if (preg_match($pattern, $text) || preg_match($pattern, $normalized) || str_contains($normalized, $word)) {
                    $matchedWords[] = $word;
                    $matchedCategories[] = $category;
                }
            }
        }

        $matchedWords = array_unique($matchedWords);
        $matchedCategories = array_unique($matchedCategories);
        $isFlagged = ! empty($matchedWords);

        // Integrasi Google Gemini Cloud AI Real
        if ($enableGoogleAi && ! $isFlagged) {
            $apiKey = PortalSetting::query()->where('key', 'pagi_google_ai_api_key')->value('value');
            $model = PortalSetting::query()->where('key', 'pagi_google_ai_model')->value('value') ?? 'gemini-1.5-flash';

            if (! empty($apiKey)) {
                $aiResult = $this->evaluateWithGoogleGeminiApi($text, $apiKey, $model);
                if ($aiResult['is_flagged']) {
                    $isFlagged = true;
                    $matchedWords = array_merge($matchedWords, $aiResult['matched_words']);
                    $matchedCategories = array_merge($matchedCategories, $aiResult['categories']);
                }
            } else {
                // Fallback ke Heuristik lokal jika API key belum diisi
                $aiScan = $this->evaluateAiContextHeuristics($normalized);
                if ($aiScan['is_flagged']) {
                    $isFlagged = true;
                    $matchedWords = array_merge($matchedWords, $aiScan['matched_words']);
                    $matchedCategories = array_merge($matchedCategories, $aiScan['categories']);
                }
            }
        }

        $severity = 'clean';
        if ($isFlagged) {
            if (in_array('judi_online', $matchedCategories) || in_array('threat', $matchedCategories) || in_array('sexual', $matchedCategories)) {
                $severity = 'critical';
            } elseif (in_array('harassment', $matchedCategories) || in_array('profanity', $matchedCategories)) {
                $severity = 'high';
            } else {
                $severity = 'medium';
            }
        }

        return [
            'is_flagged' => $isFlagged,
            'severity' => $severity,
            'matched_words' => array_values(array_unique($matchedWords)),
            'categories' => array_values(array_unique($matchedCategories)),
            'censored_text' => $this->censor($text, $matchedWords),
        ];
    }

    /**
     * Memanggil Google Gemini REST API v1beta secara Realtime
     */
    public function evaluateWithGoogleGeminiApi(string $text, string $apiKey, string $model = 'gemini-1.5-flash'): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(4)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "Anda adalah sistem moderasi konten otomatis untuk portal kampus. Analisis teks berikut apakah mengandung judi online, pelecehan/bullying, kata kasar, ancaman kekerasan, atau konten vulgar. Jawab HANYA dalam format JSON valid berikut: {\"is_toxic\": true/false, \"category\": \"judi_online/profanity/harassment/threat/sexual/none\", \"reason\": \"alasan singkat\"}. Teks: \"{$text}\"",
                                ],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $candidatesText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                $parsed = null;
                if (preg_match('/\{[\s\S]*\}/', $candidatesText, $matches)) {
                    $parsed = json_decode($matches[0], true);
                }

                if (is_array($parsed) && ($parsed['is_toxic'] ?? false)) {
                    return [
                        'is_flagged' => true,
                        'matched_words' => [$parsed['reason'] ?? 'Google AI Flagged'],
                        'categories' => [$parsed['category'] ?? 'harassment'],
                    ];
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Google Gemini API Moderation Call failed: ' . $e->getMessage());
        }

        return ['is_flagged' => false, 'matched_words' => [], 'categories' => []];
    }

    /**
     * Memindai gambar dan deskripsi postingan menggunakan Google Gemini Multimodal Vision API & Local Security Inspection
     */
    public function scanImage($imageInput, ?string $caption = null): array
    {
        // 1. Pre-scan Teks Deskripsi dulu jika ada
        $captionScan = ! empty($caption) ? $this->scan($caption) : ['is_flagged' => false, 'categories' => [], 'matched_words' => []];

        if ($captionScan['is_flagged']) {
            return [
                'is_flagged' => true,
                'severity' => $captionScan['severity'] ?? 'high',
                'categories' => $captionScan['categories'],
                'matched_words' => $captionScan['matched_words'],
                'reason' => 'Deskripsi postingan memuat kata terlarang.',
            ];
        }

        // 2. Cek apakah Google AI Vision diaktifkan & API key diisi
        $enableVisionAi = filter_var(PortalSetting::query()->where('key', 'pagi_enable_vision_ai')->value('value') ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $enableGoogleAi = filter_var(PortalSetting::query()->where('key', 'pagi_enable_google_ai')->value('value') ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $apiKey = PortalSetting::query()->where('key', 'pagi_google_ai_api_key')->value('value');
        $model = PortalSetting::query()->where('key', 'pagi_google_ai_model')->value('value') ?? 'gemini-1.5-flash';

        // 3. Konversi gambar ke base64 & MIME type
        $imageData = null;
        $mimeType = 'image/jpeg';
        $realPath = null;

        if (is_string($imageInput)) {
            if (file_exists($imageInput)) {
                $realPath = $imageInput;
                $imageData = base64_encode(file_get_contents($imageInput));
                $mimeType = mime_content_type($imageInput) ?: 'image/jpeg';
            } elseif (str_contains($imageInput, ';base64,')) {
                $parts = explode(';base64,', $imageInput, 2);
                $mimeType = str_replace('data:', '', $parts[0]);
                $imageData = $parts[1];
            }
        } elseif ($imageInput instanceof \Illuminate\Http\UploadedFile) {
            $realPath = $imageInput->getRealPath();
            $imageData = base64_encode(file_get_contents($imageInput->getRealPath()));
            $mimeType = $imageInput->getMimeType() ?: 'image/jpeg';
        }

        if (empty($imageData)) {
            return [
                'is_flagged' => false,
                'severity' => 'clean',
                'categories' => [],
                'matched_words' => [],
                'reason' => 'Format file tidak dapat dipindai.',
            ];
        }

        // Fallback: Jika Gemini Vision AI belum diaktifkan/diisi, gunakan pemindaian heuristik lokal (0 Token)
        if (! $enableVisionAi || ! $enableGoogleAi || empty($apiKey)) {
            return $this->evaluateImageLocalHeuristics($realPath, $imageData);
        }

        return $this->evaluateImageWithGoogleGeminiApi($imageData, $mimeType, $caption, $apiKey, $model);
    }

    /**
     * Pemindaian Heuristik Gambar Lokal Sisi Server (0 Token - Standalone Fallback)
     */
    private function evaluateImageLocalHeuristics(?string $filePath, ?string $base64Data): array
    {
        try {
            if ($filePath && file_exists($filePath) && function_exists('imagecreatefromstring')) {
                $rawContent = file_get_contents($filePath);
                $img = @imagecreatefromstring($rawContent);
                if ($img) {
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $sampleW = 100;
                    $sampleH = 100;

                    $thumb = imagecreatetruecolor($sampleW, $sampleH);
                    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $sampleW, $sampleH, $width, $height);

                    $skinPixels = 0;
                    $bloodPixels = 0;
                    for ($x = 0; $x < $sampleW; $x++) {
                        for ($y = 0; $y < $sampleH; $y++) {
                            $rgb = imagecolorat($thumb, $x, $y);
                            $r = ($rgb >> 16) & 0xFF;
                            $g = ($rgb >> 8) & 0xFF;
                            $b = $rgb & 0xFF;

                            // Skin tone check
                            if ($r > 95 && $g > 40 && $b > 20 && $r > $g && $r > $b && abs($r - $g) > 15) {
                                $skinPixels++;
                            }

                            // Intense blood red check (Blood & Gore)
                            if ($r > 130 && $g < 60 && $b < 60 && ($r - $g) > 60) {
                                $bloodPixels++;
                            }
                        }
                    }

                    imagedestroy($thumb);
                    imagedestroy($img);

                    $total = $sampleW * $sampleH;
                    $skinRatio = $skinPixels / $total;
                    $bloodRatio = $bloodPixels / $total;

                    if ($bloodRatio > 0.12) {
                        return [
                            'is_flagged' => true,
                            'severity' => 'critical',
                            'categories' => ['threat'],
                            'matched_words' => ['Lokal Engine: Rasio Warna Merah Darah Tinggi'],
                            'reason' => 'Gambar terdeteksi memuat konten visual berbahaya/darah (Blood & Gore).',
                        ];
                    }

                    if ($skinRatio > 0.48) {
                        return [
                            'is_flagged' => true,
                            'severity' => 'high',
                            'categories' => ['sexual'],
                            'matched_words' => ['Lokal Engine: Rasio Piksel Kulit Tinggi'],
                            'reason' => 'Gambar terdeteksi memuat konten visual sensitif/NSFW.',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore GD failures gracefully
        }

        return [
            'is_flagged' => false,
            'severity' => 'clean',
            'categories' => [],
            'matched_words' => [],
            'reason' => 'Aman (Local Engine)',
        ];
    }

    /**
     * Evaluasi gambar (Base64 + MIME) & Teks Deskripsi via Google Gemini Vision API
     */
    public function evaluateImageWithGoogleGeminiApi(string $base64Data, string $mimeType, ?string $caption, string $apiKey, string $model = 'gemini-1.5-flash'): array
    {
        try {
            $customRulesJson = PortalSetting::query()->where('key', 'pagi_custom_image_rules')->value('value');
            $customRulesStr = '';
            if ($customRulesJson) {
                $rulesArray = json_decode($customRulesJson, true);
                if (is_array($rulesArray) && ! empty($rulesArray)) {
                    $customRulesStr = "\n5. Indikator/aturan khusus terlarang dari admin kampus: " . implode(', ', $rulesArray) . ".";
                }
            }

            $prompt = "Anda adalah sistem moderasi gambar otomatis kampus. Analisis gambar ini" . ($caption ? " dan deskripsinya: \"{$caption}\"" : "") . ".
Apakah gambar/deskripsi mengandung:
1. Ketelanjangan, pornografi, atau konten vulgar (sexual)?
2. Flyer/poster/banner judi online, slot, gacor, maxwin (judi_online)?
3. Kekerasan parah, darah, atau luka parah (threat)?
4. Penipuan/scam/phishing (phishing)?" . $customRulesStr . "

Jawab HANYA dalam format JSON valid berikut (tanpa markdown backtick):
{\"is_flagged\": true/false, \"severity\": \"clean/medium/high/critical\", \"category\": \"sexual/judi_online/threat/phishing/none\", \"reason\": \"alasan singkat bahasa indonesia\"}";

            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $base64Data,
                                    ],
                                ],
                                [
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $candidatesText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                $parsed = null;
                if (preg_match('/\{[\s\S]*\}/', $candidatesText, $matches)) {
                    $parsed = json_decode($matches[0], true);
                }

                if (is_array($parsed) && ($parsed['is_flagged'] ?? false)) {
                    return [
                        'is_flagged' => true,
                        'severity' => $parsed['severity'] ?? 'high',
                        'categories' => [$parsed['category'] ?? 'sexual'],
                        'matched_words' => [$parsed['reason'] ?? 'Terdeteksi oleh AI Vision'],
                        'reason' => $parsed['reason'] ?? 'Gambar terdeteksi melanggar pedoman komunitas.',
                    ];
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Google Gemini Vision API Call failed: ' . $e->getMessage());
        }

        return [
            'is_flagged' => false,
            'severity' => 'clean',
            'categories' => [],
            'matched_words' => [],
            'reason' => 'Aman',
        ];
    }

    /**
     * Menguji koneksi API Key Google Gemini
     */
    public function testGoogleAiConnection(string $apiKey, string $model = 'gemini-flash-latest'): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => 'Tes koneksi sistem moderasi FMIKOM.'],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Koneksi ke Google Gemini AI API Berhasil! (Model: ' . $model . ')'];
            }

            $errorMsg = $response->json('error.message') ?? 'HTTP status ' . $response->status();
            return ['success' => false, 'message' => 'Gagal terhubung ke Google Gemini: ' . $errorMsg];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Kesalahan koneksi: ' . $e->getMessage()];
        }
    }

    /**
     * Memanggil Google Gemini ListModels API untuk mendapatkan daftar model aktif resmi dari akun pengguna (Dokploy Dynamic Fetching)
     */
    public function fetchAvailableGeminiModels(string $apiKey): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(6)
                ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");

            if ($response->successful()) {
                $rawModels = $response->json('models', []);
                $formattedModels = [];

                // Opsi Alias Rekomendasi Resmi Google & Dokploy di urutan teratas
                $formattedModels[] = [
                    'id' => 'gemini-flash-latest',
                    'name' => 'gemini-flash-latest ⭐ (Dokploy Default - Otomatis Best Flash)',
                ];
                $formattedModels[] = [
                    'id' => 'gemini-pro-latest',
                    'name' => 'gemini-pro-latest (Dokploy Default - Otomatis Best Pro)',
                ];

                $addedIds = ['gemini-flash-latest', 'gemini-pro-latest'];

                foreach ($rawModels as $m) {
                    $name = $m['name'] ?? '';
                    $cleanId = str_replace('models/', '', $name);
                    $methods = $m['supportedGenerationMethods'] ?? [];

                    // Filter model yang mendukung generateContent & belum ada di daftar alias
                    if (in_array('generateContent', $methods) && !in_array($cleanId, $addedIds)) {
                        $displayName = $cleanId;
                        if (isset($m['displayName'])) {
                            $displayName .= " ({$m['displayName']})";
                        }
                        $formattedModels[] = [
                            'id' => $cleanId,
                            'name' => $displayName,
                        ];
                        $addedIds[] = $cleanId;
                    }
                }

                return [
                    'success' => true,
                    'models' => $formattedModels,
                    'message' => count($formattedModels) . ' model berhasil dimuat dari Google AI Studio!',
                ];
            }

            $errorMsg = $response->json('error.message') ?? 'HTTP status ' . $response->status();
            return ['success' => false, 'models' => [], 'message' => 'Gagal memuat model dari Google: ' . $errorMsg];
        } catch (\Throwable $e) {
            return ['success' => false, 'models' => [], 'message' => 'Kesalahan koneksi: ' . $e->getMessage()];
        }
    }

    /**
     * Pemindaian Heuristik AI Konteks (Pelecehan & Bullying Terselubung)
     */
    private function evaluateAiContextHeuristics(string $normalized): array
    {
        $toxicPatterns = [
            'harassment' => [
                '/\b(bunuh\s+diri|mati\s+aja|benci\s+banget|muka\s+jelek|anak\s+haram|ga\s+guna)\b/iu',
            ],
            'threat' => [
                '/ (tak\s+hajar|tak\s+pateni|awas\s+lu|tak\s+seret|gua\nincang)\b/iu',
            ],
            'judi_online' => [
                '/\b(depo\s+\d+|bonus\s+new\s+member|link\s+alternatif|zeus\s+olympus)\b/iu',
            ],
        ];

        $matchedWords = [];
        $categories = [];

        foreach ($toxicPatterns as $cat => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $normalized, $matches)) {
                    $matchedWords[] = $matches[0];
                    $categories[] = $cat;
                }
            }
        }

        return [
            'is_flagged' => ! empty($matchedWords),
            'matched_words' => $matchedWords,
            'categories' => $categories,
        ];
    }

    /**
     * Menyensor kata-kata terlarang di dalam teks dengan asteris (misal: anjing -> a***ng)
     */
    public function censor(string $text, array $wordsToCensor = []): string
    {
        if (empty($wordsToCensor)) {
            $dict = $this->getBannedWords();
            $wordsToCensor = array_keys($dict);
        }

        $censored = $text;
        foreach ($wordsToCensor as $word) {
            if (mb_strlen($word) <= 2) {
                $mask = str_repeat('*', mb_strlen($word));
            } else {
                $first = mb_substr($word, 0, 1);
                $last = mb_substr($word, -1);
                $middle = str_repeat('*', mb_strlen($word) - 2);
                $mask = $first . $middle . $last;
            }

            $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
            $censored = preg_replace($pattern, $mask, $censored);
        }

        return $censored;
    }
}
