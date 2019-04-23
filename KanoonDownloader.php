<?php

class KanoonDownloader
{

    public static function downloadQuestions($date, $dept, $path = '') {
        $hdDate = self::getHdDate($date);
        if(empty($hdDate)) {
            return 'Exam not found.';
        }
        if(empty($path)) {
            $path = $date;
        }
        foreach([$path, $path.'/'.$dept, $path.'/'.$dept.'/Questions', $path.'/'.$dept.'/Answers'] as $path_to_make) {
            if(!file_exists($path_to_make)) {
                mkdir($path_to_make);
            }
        }
        $i = 1;
        while(true) {
            $question_path = $path.'/'.$dept.'/Questions'.'/'.$i.'.gif';
            self::curl_download_file('http://www.kanoon.ir/Downloads/Question/'.$dept.'/'.$hdDate.'/'.$i, $question_path);
            if(filesize($question_path) == 1554) {
                unlink($question_path);
                break;
            }
            self::curl_download_file('http://www.kanoon.ir/Downloads/Answer/'.$dept.'/'.$hdDate.'/'.$i, $path.'/'.$dept.'/Answers'.'/'.$i.'.gif');
            $i++;
        }
        if($i == 1) {
            self::deleteDirectory($date);
            return 'Exam not found.';
        }
        return 'Success!';
    }

    private static function getHdDate($date) {
        $hdDate = self::curl_get_contents('www.kanoon.ir/Public/Mistakes?mc=1&gc=1&td='.$date.'&qid=1');
        if(preg_match('/Question\/1\/([\w]+)/', $hdDate, $hdDate)) {
            return $hdDate[1];
        }
        return false;
    }

    private static function curl_download_file($url, $path) {
        $fp = fopen($path, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch); 
        curl_close($ch);
        fclose($fp);
    }

    private static function curl_get_contents($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private static function deleteDirectory($dir) {
        foreach(glob($dir) as $file) {
            if(is_dir($file)) { 
                self::deleteDirectory($file.'/*');
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }

}
