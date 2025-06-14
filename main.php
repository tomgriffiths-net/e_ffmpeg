<?php
class e_ffmpeg{
    public static function init(){
        if(self::path() === false){
            echo "Would you like to install Ffmpeg? It may be required for certain functionalities.\n";
            if(user_input::yesNo()){
                self::download();
            }
        }
    }
    public static function path(string $name = "ffmpeg"):string|bool{
        if($name === "ffmpeg" || $name === "ffprobe" || $name === "ffplay"){
            $fileName = getcwd() . "\\ffmpeg\\ffmpeg-master-latest-win64-gpl\\bin\\" . $name . ".exe";
            if(is_file($fileName)){
                return $fileName;
            }
        }
        return false;
    }
    public static function download():bool{
        $file = "ffmpeg/ffmpeg-master-latest-win64-gpl.zip";
        if(!downloader::downloadFile("https://github.com/BtbN/FFmpeg-Builds/releases/download/latest/ffmpeg-master-latest-win64-gpl.zip",$file)){
            echo "Failed to download ffmpeg zip file\n";
            return false;
        }

        echo "Unzipping ffmpeg zip...\n";
        $zip = new ZipArchive;
        $result = $zip->open($file);

        if($result !== true){
            echo "Failed to open downloaded zip file\n";
            return false;
        }

        if(!$zip->extractTo('ffmpeg')){
            echo "Failed to unzip ffmpeg\n";
            return false;
        }

        $zip->close();
        unlink("ffmpeg\\ffmpeg-master-latest-win64-gpl.zip");

        if(!is_string(self::path())){
            echo "Failed to locate ffmpeg.exe\n";
            return false;
        }
        
        mklog(1,'Ffmpeg downloaded');
        
        return true;
    }
}