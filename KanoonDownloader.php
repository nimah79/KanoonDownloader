<?php

class KanoonDownloader
{
    private static $dept_codes = [
        1 => [1, 2, 3, 4, 5, 6, 7, 9],
        2 => [21, 22, 23, 24, 25, 26, 29, 30],
        3 => [27, 28, 31, 33],
        4 => [35, 36, 41, 42, 43, 45, 46],
        5 => [53, 55, 59, 66, 67, 68, 69],
        6 => [83, 85, 88, 89, 93],
    ];

    public static function downloadQuestions($date, $dept, $path = '')
    {
        $mc = self::getMC($dept);
        if (empty($mc)) {
            return 'Invalid dept code.';
        }
        $hdDate = self::getHdDate($date, $mc, $dept);
        if (empty($hdDate)) {
            return 'Exam not found.';
        }
        if (empty($path)) {
            $path = $date;
        }
        foreach ([$path, $path.'/'.$dept, $path.'/'.$dept.'/Questions', $path.'/'.$dept.'/Answers'] as $path_to_make) {
            if (!file_exists($path_to_make)) {
                mkdir($path_to_make);
            }
        }
        $i = 1;
        while (true) {
            $question_path = $path.'/'.$dept.'/Questions'.'/'.$i.'.gif';
            self::curl_download_file('http://www.kanoon.ir/Downloads/Question/'.$dept.'/'.$hdDate.'/'.$i, $question_path);
            if (filesize($question_path) == 1554) {
                unlink($question_path);
                break;
            }
            self::curl_download_file('http://www.kanoon.ir/Downloads/Answer/'.$dept.'/'.$hdDate.'/'.$i, $path.'/'.$dept.'/Answers'.'/'.$i.'.gif');
            $i++;
        }
        if ($i == 1) {
            self::deleteDirectory($date);

            return 'Exam not found.';
        }

        return 'Success!';
    }

    private static function getHdDate($date, $mc, $dept)
    {
        $hdDate = self::curl_get_contents('www.kanoon.ir/Public/Mistakes?mc='.$mc.'&gc='.$dept.'&td='.$date.'&qid=1');
        if (preg_match('/Question\/'.$dept.'\/([\w]+)/', $hdDate, $hdDate)) {
            return $hdDate[1];
        }

        return false;
    }

    private static function curl_download_file($url, $path)
    {
        $fp = fopen($path, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    private static function curl_get_contents($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private static function deleteDirectory($dir)
    {
        foreach (glob($dir) as $file) {
            if (is_dir($file)) {
                self::deleteDirectory($file.'/*');
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }

    private static function getMC($gc)
    {
        foreach (self::$dept_codes as $mc => $gcs) {
            if (in_array($gc, $gcs)) {
                return $mc;
            }
        }

        return false;
    }
}
