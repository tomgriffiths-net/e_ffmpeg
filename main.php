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
    public static function path($name = "ffmpeg"):string|bool{
        if($name === "ffmpeg" || $name === "ffprobe" || $name === "ffplay"){
            $fileName = getcwd() . "\\ffmpeg\\ffmpeg-master-latest-win64-gpl\\bin\\" . $name . ".exe";
            if(is_file($fileName)){
                return $fileName;
            }
        }
        return false;
    }
    public static function download(){
        downloader::downloadFile("https://github.com/BtbN/FFmpeg-Builds/releases/download/latest/ffmpeg-master-latest-win64-gpl.zip","ffmpeg/ffmpeg-master-latest-win64-gpl.zip");
        shell_exec('powershell -command "Expand-Archive -Path ffmpeg\\ffmpeg-master-latest-win64-gpl.zip -DestinationPath ffmpeg -Force"');
        unlink("ffmpeg\\ffmpeg-master-latest-win64-gpl.zip");
        mklog('general','Ffmpeg downloaded',false);
    }
}