# Stream Metadata Fetcher

This PHP script fetches metadata from an online radio stream, including the current song title, artist, album art, station name, genre, bitrate, and various other stream details.

## Features

- Retrieve the current song title and artist from the radio stream.
- Fetch album art from iTunes.
- Get additional information about the radio station such as name, genre, bitrate, and more.
- Determine the type of streaming server (Shoutcast, Icecast, etc.).
- Log and return all available headers for comprehensive metadata.

## Usage

1. **Upload to your server:**
    - Upload the `Radio.php` file to your PHP-enabled web server.

2. **Access the script via URL:**
    - Navigate to the URL where you uploaded the script, providing the `url` parameter with the streaming URL and optionally the `interval` parameter.
    - Example:
      ```
      http://yourserver.com/Radio.php?url=http://yourstreamingurl.com/stream
      ```
    - You can also specify an interval (default is 19200):
      ```
      http://yourserver.com/Radio.php?url=http://yourstreamingurl.com/stream&interval=16000
      ```

## Parameters

- `url` (required): The streaming URL of the radio station.
- `interval` (optional): The interval in bytes to read the stream metadata.

## Example Response

```json
{
    "songtitle": "Artist - Song Title",
    "artist": "Artist",
    "song": "Song Title",
    "artwork": "https://path.to.album.art/512x512bb.jpg",
    "station_name": "Radio Station Name",
    "genre": "Genre",
    "bitrate": 128,
    "stream_type": "Shoutcast",
    "content_type": "audio/mpeg",
    "current_listeners": 42,
    "peak_listeners": 100,
    "max_listeners": 500,
    "audio_info": "bitrate=128",
    "server": "Icecast 2.4.0-kh10",
    "url": "http://station.url",
    "public": 1,
    "description": "Station Description",
    "cache_control": "no-cache",
    "expires": "Mon, 26 Jul 1997 05:00:00 GMT",
    "pragma": "no-cache",
    "date": "Tue, 25 Jul 2023 12:00:00 GMT",
    "metaint": 16000,
    "session_id": "1234567890",
    "notice1": "Notice Message 1",
    "notice2": "Notice Message 2",
    "irc_notify": "IRC Notify",
    "ircnick": "IRCNick",
    "ircchan": "#IRCChannel",
    "ircserver": "irc.server.com",
    "location": "http://redirect.url",
    "headers": {
        "icy-name": "Radio Station Name",
        "icy-genre": "Genre",
        "icy-br": "128",
        "Content-Type": "audio/mpeg",
        "icy-listeners": "42",
        "icy-peak-listeners": "100",
        "icy-max-listeners": "500",
        "icy-audio-info": "bitrate=128",
        "Server": "Icecast 2.4.0-kh10",
        "icy-url": "http://station.url",
        "icy-pub": "1",
        "icy-description": "Station Description",
        "Cache-Control": "no-cache",
        "Expires": "Mon, 26 Jul 1997 05:00:00 GMT",
        "Pragma": "no-cache",
        "Date": "Tue, 25 Jul 2023 12:00:00 GMT",
        "icy-metaint": "16000",
        "icy-session-id": "1234567890",
        "icy-notice1": "Notice Message 1",
        "icy-notice2": "Notice Message 2",
        "icy-irc-notify": "IRC Notify",
        "icy-ircnick": "IRCNick",
        "icy-ircchan": "#IRCChannel",
        "icy-ircserver": "irc.server.com",
        "Location": "http://redirect.url"
    }
}
