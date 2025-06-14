# e_ffmpeg
e_ffmpeg is a PHP-CLI package that provides functionality to download the popular video encoding software ffmpeg from official sources. This is not ffmpeg, just a download aid.

# Functions

- **init**: Asks the user if they want to download ffmpeg if they have not got it installed.

- **download():bool**: Downloads ffmpeg, returns true on success and false on failure.

- **path(string $name="ffmpeg"):string|bool**: Returns the absolute path to the ffmpeg, ffprobe, or ffplay executable depending on the $name. Returns false on failure.