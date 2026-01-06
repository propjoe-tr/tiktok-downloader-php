# 🎵 TikTok Video Downloader PHP

A lightweight PHP library to download TikTok videos without watermark using ssstik.io API.

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## ✨ Features

- Download TikTok videos without watermark
- Extract MP3 audio from videos
- Simple REST API endpoint
- No dependencies (pure PHP + cURL)
- CLI support

## 📦 Installation

```bash
git clone https://github.com/YOUR_USERNAME/tiktok-downloader-php.git
```

Or just download `ssstik.php` and include it in your project.

## 🚀 Usage

### As REST API

```
GET /ssstik.php?url=https://www.tiktok.com/@user/video/123456
```

Response:
```json
{
  "author": "Username",
  "description": "Video description",
  "video_no_watermark": "https://tikcdn.io/ssstik/...",
  "audio_mp3": "https://tikcdn.io/ssstik/m/..."
}
```

### In PHP Code

```php
require_once 'ssstik.php';

$downloader = new SsstikDownloader();
$result = $downloader->getDownloadLinks('https://www.tiktok.com/@user/video/123456');

// Get video URL
echo $result['video_no_watermark'];

// Download video to file
$downloader->downloadVideo($result['video_no_watermark'], 'video.mp4');
```

### Command Line

```bash
php ssstik.php "https://www.tiktok.com/@user/video/123456"
```

## 📋 API Response

| Field | Description |
|-------|-------------|
| `author` | Video creator's username |
| `description` | Video description/caption |
| `video_no_watermark` | Direct download URL (no watermark) |
| `audio_mp3` | MP3 audio download URL |

## ⚙️ Requirements

- PHP 7.4 or higher
- cURL extension enabled

## 📝 License

MIT License - feel free to use in your projects.

## ⚠️ Disclaimer

This tool is for educational purposes only. Please respect TikTok's terms of service and content creators' rights. Download only videos you have permission to use.

## 🤝 Contributing

Pull requests are welcome! Feel free to open an issue for bugs or feature requests.
