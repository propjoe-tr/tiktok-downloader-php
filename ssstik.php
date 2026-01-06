<?php
/**
 * ssstik.io TikTok Video Downloader
 * PHP cURL ile
 */

class SsstikDownloader
{
    private $baseUrl = 'https://ssstik.io';
    
    public function getDownloadLinks(string $tiktokUrl): array
    {
        $ttToken = $this->getTtToken();
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/abc?url=dl',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'id' => $tiktokUrl,
                'locale' => 'en',
                'tt' => $ttToken,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'HX-Request: true',
                'HX-Target: target',
                'HX-Current-URL: https://ssstik.io/en-1',
                'HX-Trigger: _gcaptcha_pt',
                'Origin: https://ssstik.io',
                'Referer: https://ssstik.io/en-1',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36',
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Request failed: HTTP $httpCode");
        }
        
        return $this->parseResponse($response);
    }
    
    private function getTtToken(): string
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/en-1',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        $html = curl_exec($ch);
        curl_close($ch);
        
        if (preg_match('/name="tt"\s+value="([^"]+)"/', $html, $matches)) {
            return $matches[1];
        }
        
        return 'UG9IWGs2';
    }
    
    private function parseResponse(string $html): array
    {
        $result = [
            'author' => null,
            'description' => null,
            'video_no_watermark' => null,
            'audio_mp3' => null,
        ];
        
        if (preg_match('/<h2>([^<]+)<\/h2>/', $html, $m)) {
            $result['author'] = trim($m[1]);
        }
        
        if (preg_match('/<p class="maintext">([^<]+)<\/p>/', $html, $m)) {
            $result['description'] = trim($m[1]);
        }
        
        if (preg_match('/href="(https:\/\/tikcdn\.io\/ssstik\/[^"]+)"[^>]*class="[^"]*without_watermark(?!_hd)[^"]*"/', $html, $m)) {
            $result['video_no_watermark'] = $m[1];
        }
        
        if (!$result['video_no_watermark']) {
            if (preg_match('/<a[^>]*href="(https:\/\/tikcdn\.io\/ssstik\/\d+\?[^"]+)"/', $html, $m)) {
                $result['video_no_watermark'] = $m[1];
            }
        }
        
        if (preg_match('/href="(https:\/\/tikcdn\.io\/ssstik\/m\/[^"]+)"/', $html, $m)) {
            $result['audio_mp3'] = $m[1];
        }
        
        return $result;
    }
    
    public function downloadVideo(string $url, string $filename): bool
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
        ]);
        
        $data = curl_exec($ch);
        curl_close($ch);
        
        if ($data) {
            file_put_contents($filename, $data);
            return true;
        }
        
        return false;
    }
}

if (php_sapi_name() === 'cli') {
    $tiktokUrl = $argv[1] ?? null;
    
    if (!$tiktokUrl) {
        echo "Kullanım: php ssstik.php \"TIKTOK_URL\"\n";
        echo "Örnek: php ssstik.php \"https://www.tiktok.com/@user/video/123456\"\n";
        exit(1);
    }
    
    $downloader = new SsstikDownloader();
    
    try {
        echo "İşleniyor: $tiktokUrl\n\n";
        $links = $downloader->getDownloadLinks($tiktokUrl);
        
        echo "Yazar: {$links['author']}\n";
        echo "Açıklama: {$links['description']}\n\n";
        echo "--- İndirme Linkleri ---\n";
        echo "Video (Watermark'sız): {$links['video_no_watermark']}\n";
        echo "MP3: {$links['audio_mp3']}\n";
        
    } catch (Exception $e) {
        echo "Hata: " . $e->getMessage() . "\n";
    }
}

else {
    header('Content-Type: application/json');
    
    $tiktokUrl = $_GET['url'] ?? $_POST['url'] ?? null;
    
    if (!$tiktokUrl) {
        echo json_encode(['error' => 'url parametresi gerekli']);
        exit;
    }
    
    $downloader = new SsstikDownloader();
    
    try {
        $links = $downloader->getDownloadLinks($tiktokUrl);
        echo json_encode($links, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
