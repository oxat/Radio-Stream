<?php
class StreamManager {

    public function getStreamTitle($streamingUrl, $interval = 19200): ?string {
        $context = stream_context_create(['http' => ['header' => "Icy-MetaData: 1\r\nUser-Agent: Mozilla/5.0", 'timeout' => 30]]);
        $stream = @fopen($streamingUrl, 'r', false, $context);
        if (!$stream) return null;

        foreach ($http_response_header as $header) {
            if (stripos($header, 'icy-metaint') !== false) {
                $metaDataInterval = (int)trim(explode(':', $header)[1]);
                break;
            }
        }
        if (!isset($metaDataInterval)) return null;

        while (!feof($stream)) {
            fread($stream, $metaDataInterval);
            $buffer = fread($stream, $interval);
            if (($titleIndex = strpos($buffer, 'StreamTitle=')) !== false) {
                fclose($stream);
                return substr($buffer, $titleIndex + 12, strpos($buffer, ';', $titleIndex) - $titleIndex - 12);
            }
        }
        fclose($stream);
        return null;
    }

    public function extractArtistAndSong($title): array {
        $parts = array_map('trim', explode(' - ', trim($title, "'")));
        return count($parts) == 2 ? $parts : ["Unknown Artist", "Unknown Title"];
    }

    public function getAlbumArt($artist, $song): ?string {
        $response = @file_get_contents('https://itunes.apple.com/search?term=' . urlencode("$artist $song") . '&media=music&limit=1');
        if (!$response) return null;

        $data = json_decode($response, true);
        return $data['resultCount'] > 0 ? str_replace('100x100bb', '512x512bb', $data['results'][0]['artworkUrl100']) : null;
    }

    public function getAdditionalInfo($streamingUrl): array {
        $headers = get_headers($streamingUrl, 1);
        return [
            'station_name' => $headers['icy-name'] ?? null,
            'genre' => $headers['icy-genre'] ?? null,
            'bitrate' => $headers['icy-br'] ?? null
        ];
    }
}

header('Content-Type: application/json');

$url = $_GET['url'] ?? '';
$interval = $_GET['interval'] ?? 19200;

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(["error" => "Invalid URL"]);
    exit;
}

$streamManager = new StreamManager();
$title = $streamManager->getStreamTitle($url, $interval);
$additionalInfo = $streamManager->getAdditionalInfo($url);

if ($title) {
    [$artist, $song] = $streamManager->extractArtistAndSong($title);
    $artUrl = $streamManager->getAlbumArt($artist, $song);

    echo json_encode([
        "songtitle" => $title,
        "artist" => $artist,
        "song" => $song,
        "artwork" => $artUrl,
        "station_name" => $additionalInfo['station_name'],
        "genre" => $additionalInfo['genre'],
        "bitrate" => $additionalInfo['bitrate']
    ]);
} else {
    echo json_encode(["error" => "Failed to retrieve stream title"]);
}
?>
