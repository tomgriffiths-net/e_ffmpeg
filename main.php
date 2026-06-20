<?php
class e_ffmpeg{
    private static $url = 'https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-full.7z';

    public static function init(){
        if(!self::path()){
            echo "A package requires ffmpeg to be installed, do you want to install it?\n";
            if(user_input::yesNo()){
                self::install();
            }
            return;
        }

        if(!self::isRegistered()){
            echo "A package requires ffmpeg, it is installed but not on user PATH, would you like to add it?\n";
            if(user_input::yesNo()){
                self::register();
            }
        }
    }

    public static function isRegistered():bool{
        exec('where ffmpeg 2>nul', $_, $code);
        return $code === 0;
    }
    public static function path(string $exe="ffmpeg"):?string{
        $exe = strtolower($exe);
        if(!in_array($exe, ['ffmpeg','ffprobe','ffplay'])){
            return null;
        }

        $base = str_replace('\\', '/', self::baseDir());
        $hits = glob("$base/*/bin/$exe.exe");

        if(!is_array($hits) || empty($hits)){
            return null;
        }

        // Highest version first.  $b,$a (not $a,$b) makes it descending.
        usort($hits, fn($a, $b) => version_compare(self::verOf($b), self::verOf($a)));

        return str_replace('/', '\\', $hits[0]);
    }
    public static function version(string $exe="ffmpeg"):?string{
        $path = self::path($exe);
        return $path ? basename(dirname(dirname($path))) : null;
    }

    public static function install():bool{
        if(self::path()){
            mklog(1, "ffmpeg already installed (" . self::version() . ")");
            return true;
        }

        $base = self::baseDir();
        if(!files::ensureFolder($base)){
            mklog(3, "Failed to ensure base install directory");
            return false;
        }

        // Download to temp — the filename doesn't matter, we read the version from the folder later.
        $tmp = 'temp\\e_ffmpeg';
        if(!files::ensureFolder($base)){
            mklog(3, "Failed to ensure temporary download directory");
            return false;
        }
        $archive = $tmp . '\\ffmpeg-latest-' . time() . '.7z';

        if(!downloader::downloadFile(self::$url, $archive)){
            @unlink($archive);
            return false;
        }

        // error page downloaded
        if(filesize($archive) < 10000000){
            mklog(3, "ffmpeg download too small, likely failed");
            @unlink($archive);
            return false;
        }

        echo "Extracting ffmpeg build using 7zip...\n";
        // Extract into the base; the archive holds one top folder (ffmpeg-<ver>-full_build\)
        // so it lands as ...\ffmpeg\ffmpeg-<ver>-full_build\bin\ffmpeg.exe automatically.
        exec('7z x ' . escapeshellarg($archive) . ' -o' . escapeshellarg($base) . ' -y', $out, $code);
        @unlink($archive);

        if($code > 1){   // 0 = ok, 1 = warning, 2+ = real error
            mklog(3, "7z extraction failed (code $code):\n" . implode("\n", $out));
            return false;
        }

        if(!self::path()){
            mklog(3, "ffmpeg.exe not found after extraction");
            return false;
        }

        mklog(1, "ffmpeg installed (" . self::version() . ")");
        return self::register();
    }
    public static function register():bool{
        if(self::isRegistered()){
            mklog(1, "Ffmpeg already on PATH");
            return true;
        }

        $path = self::path();
        if(!$path){
            mklog(3, "Cannot register ffmpeg because it is not installed in the expected location");
            return false;
        }
        $dir = dirname($path);

        $ps = "[Environment]::SetEnvironmentVariable('Path', ([Environment]::GetEnvironmentVariable('Path','User').TrimEnd(';') + ';$dir'), 'User')";
        exec('powershell -NoProfile -Command ' . escapeshellarg($ps), $_, $psCode);
        if($psCode !== 0){
            mklog(3, "failed to write user PATH (powershell exit $psCode)");
            return false;
        }

        if(!putenv('PATH=' . getenv('PATH') . ';' . $dir)){
            mklog(2, "Added 7z to user PATH but could not update current php process, please restart PHP-CLI to see changes");
        }

        mklog(1, "registered ffmpeg on PATH ($dir)");
        return true;
    }

    // ...\ffmpeg-8.1.1-full_build\bin\ffmpeg.exe  ->  "8.1.1"
    private static function verOf(string $exePath):string{
        $folder = basename(dirname(dirname($exePath)));        // ffmpeg-8.1.1-full_build
        return preg_match('/(\d+(?:\.\d+)*)/', $folder, $m) ? $m[1] : '0';
    }
    private static function baseDir(): string {
        return getenv('LOCALAPPDATA') . '\\Programs\\ffmpeg';
    }
}