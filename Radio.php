<?php
class StreamManager {
    private $timeout = 5; // Set timeout to 5 seconds

    public function getStreamTitle($streamingUrl, $interval = 19200): ?string {
        $context = stream_context_create([
            'http' => [
                'header' => "Icy-MetaData: 1\r\nUser-Agent: Mozilla/5.0",
                'timeout' => $this->timeout
            ]
        ]);
        $stream = @fopen($streamingUrl, 'r', false, $context);
        if (!$stream) return null;

        $metaDataInterval = null;
        foreach ($http_response_header as $header) {
            if (stripos($header, 'icy-metaint') !== false) {
                $metaDataInterval = (int)trim(explode(':', $header)[1]);
                break;
            }
        }
        if ($metaDataInterval === null) {
            fclose($stream);
            return null;
        }

        stream_set_timeout($stream, $this->timeout);
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
        // Try iTunes first
        $response = @file_get_contents('https://itunes.apple.com/search?term=' . urlencode("$artist $song") . '&media=music&limit=1');
        if ($response) {
            $data = json_decode($response, true);
            if ($data['resultCount'] > 0) {
                return str_replace('100x100bb', '512x512bb', $data['results'][0]['artworkUrl100']);
            }
        }

        // Fallback to CoverArtArchive using MusicBrainz
        $response = @file_get_contents('https://musicbrainz.org/ws/2/recording/?query=artist:' . urlencode($artist) . '%20AND%20recording:' . urlencode($song) . '&fmt=json');
        if ($response) {
            $data = json_decode($response, true);
            if (!empty($data['recordings'])) {
                $mbid = $data['recordings'][0]['releases'][0]['id'];
                $coverResponse = @file_get_contents('https://coverartarchive.org/release/' . $mbid);
                if ($coverResponse) {
                    $coverData = json_decode($coverResponse, true);
                    if (!empty($coverData['images'][0]['thumbnails']['large'])) {
                        return $coverData['images'][0]['thumbnails']['large'];
                    }
                }
            }
        }

        return null;
    }

    public function getAdditionalInfo($streamingUrl): array {
        $headers = @get_headers($streamingUrl, 1);
        $streamType = $this->detectStreamType($headers);

        // Log headers to error log for debugging purposes
        error_log(print_r($headers, true));

        return [
            'station_name' => $headers['icy-name'] ?? null,
            'genre' => $headers['icy-genre'] ?? null,
            'bitrate' => $headers['icy-br'] ?? null,
            'stream_type' => $streamType,
            'content_type' => $headers['Content-Type'] ?? null,
            'current_listeners' => $headers['icy-listeners'] ?? null,
            'peak_listeners' => $headers['icy-peak-listeners'] ?? null,
            'max_listeners' => $headers['icy-max-listeners'] ?? null,
            'audio_info' => $headers['icy-audio-info'] ?? null,
            'server' => $headers['Server'] ?? null,
            'url' => $headers['icy-url'] ?? null,
            'public' => $headers['icy-pub'] ?? null,
            'description' => $headers['icy-description'] ?? null,
            'cache_control' => $headers['Cache-Control'] ?? null,
            'expires' => $headers['Expires'] ?? null,
            'pragma' => $headers['Pragma'] ?? null,
            'date' => $headers['Date'] ?? null,
            'metaint' => $headers['icy-metaint'] ?? null,
            'session_id' => $headers['icy-session-id'] ?? null,
            'notice1' => $headers['icy-notice1'] ?? null,
            'notice2' => $headers['icy-notice2'] ?? null,
            'irc_notify' => $headers['icy-irc-notify'] ?? null,
            'ircnick' => $headers['icy-ircnick'] ?? null,
            'ircchan' => $headers['icy-ircchan'] ?? null,
            'ircserver' => $headers['icy-ircserver'] ?? null,
            'location' => $headers['Location'] ?? null
        ];
    }

    private function detectStreamType($headers): string {
        if (isset($headers['Server'])) {
            $serverHeader = strtolower($headers['Server']);
            if (strpos($serverHeader, 'shoutcast') !== false) {
                return 'Shoutcast';
            } elseif (strpos($serverHeader, 'icecast') !== false) {
                return 'Icecast';
            }
        }
        return 'Unknown';
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

try {
    $title = $streamManager->getStreamTitle($url, $interval);
    if (!$title) {
        throw new Exception("Failed to retrieve stream title");
    }
    $additionalInfo = $streamManager->getAdditionalInfo($url);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

[$artist, $song] = $streamManager->extractArtistAndSong($title);
$artUrl = $streamManager->getAlbumArt($artist, $song);

echo json_encode([
    "songtitle" => $title,
    "artist" => $artist,
    "song" => $song,
    "artwork" => $artUrl,
    "station_name" => $additionalInfo['station_name'],
    "genre" => $additionalInfo['genre'],
    "bitrate" => $additionalInfo['bitrate'],
    "stream_type" => $additionalInfo['stream_type'],
    "content_type" => $additionalInfo['content_type'],
    "current_listeners" => $additionalInfo['current_listeners'],
    "peak_listeners" => $additionalInfo['peak_listeners'],
    "max_listeners" => $additionalInfo['max_listeners'],
    "audio_info" => $additionalInfo['audio_info'],
    "server" => $additionalInfo['server'],
    "url" => $additionalInfo['url'],
    "public" => $additionalInfo['public'],
    "description" => $additionalInfo['description'],
    "cache_control" => $additionalInfo['cache_control'],
    "expires" => $additionalInfo['expires'],
    "pragma" => $additionalInfo['pragma'],
    "date" => $additionalInfo['date'],
    "metaint" => $additionalInfo['metaint'],
    "session_id" => $additionalInfo['session_id'],
    "notice1" => $additionalInfo['notice1'],
    "notice2" => $additionalInfo['notice2'],
    "irc_notify" => $additionalInfo['irc_notify'],
    "ircnick" => $additionalInfo['ircnick'],
    "ircchan" => $additionalInfo['ircchan'],
    "ircserver" => $additionalInfo['ircserver'],
    "location" => $additionalInfo['location'],
    "headers" => $additionalInfo['headers'] // Include all headers in the response for inspection
]);
?>
