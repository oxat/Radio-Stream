# Stream Metadata Fetcher

This PHP script fetches metadata from an online radio stream, including the current song title, artist, album art, station name, genre, and bitrate.

## Features

- Retrieve the current song title and artist from the radio stream.
- Fetch album art from iTunes.
- Get additional information about the radio station such as name, genre, and bitrate.

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
    "bitrate": 128
}
