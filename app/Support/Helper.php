<?php
namespace App\Support;

class Helper
{
    static $number = 1;

    public static function check_cyr($string)
    {
        //   setlocale (LC_ALL, "ru_RU.UTF-8");

        // //   $enc = mb_detect_encoding($string, mb_list_encodings(), true);
        // //   var_dump($enc);
        // //   if ($enc===false){
        // //       //could not detect encoding
        // //   }
        // //   else if ($enc!=="UTF-8"){
        // //       $string = mb_convert_encoding($string, "UTF-8", $enc);
        // //   }
        // //   else {
        // //       //UTF-8 detected
        // //   }


        //   var_dump($string);


        //     print(mb_detect_encoding ($string)) ."</br>";

        //   $string2 = mb_convert_encoding($string, "UTF-8");
        //   var_dump($string2);
        // //   $from_cp = 'CP866'; # из какой кодировки
        // //   $to_cp = 'windows-1251'; # в какую, по умолчанию используется эта кодировка
        // //   echo   mb_convert_encoding($string, $from_cp, $to_cp);
        //   echo   mb_convert_encoding($string, 'utf-8', mb_detect_encoding($string));
        // // print(mb_detect_encoding ($string));
        //  echo   iconv( 'CP866','UTF-8', $string) ;
        // // echo   mb_substr($string, 0, 7, "CP866")."</br>";
        // //   echo mb_detect_encoding($string)."</br>";
        // //   echo iconv( "utf-8","windows-1251", $string);

        // // echo  iconv('UTF-8', 'CP866', $string)."</br>";;

        // echo mb_convert_encoding($string, 'UTF-8', 'UTF-8, windows-1251') . '<br>';
        return $contains_cyrillic = (bool) preg_match('/[\p{Cyrillic}]/u', $string);
    }

    public static function check_image($extention)
    {
        $filetypes = array("jpg", "jpeg", "png", "bmp", "gif");
        if (in_array(strtolower($extention), $filetypes)) {
            return true;
        }
        return false;
    }

    public static function rename_files_in_html($rename_files, $html_files)
    {
        foreach ($html_files as $html) {
            $homepage = file_get_contents($html->getRealPath());
            $text = $homepage;
            preg_match_all('/<img[^>]+src="?\'?([^"\']+)"?\'?[^>]*>/i', $homepage, $images, PREG_SET_ORDER);
            foreach ($images as $value) {

                $pieces = explode("/", $value[1]);
                foreach ($rename_files as $k => $v) {
                    if ($v->getBasename() == end($pieces)) {
                        $temppath = str_replace(end($pieces), $v->good_name, $value[0]);
                        $text = str_replace($value[0], $temppath, $text);
                        // var_dump($temppath);
                        break;
                    }
                }
                file_put_contents($html->getRealPath(), $text);
            }
        }
    }

    public static function check_exist_name($filename, $images_files)
    {
        foreach ($images_files as $file) {
            if ($file->getBasename() == $filename) {
                $filename_new = current(explode(".", $filename));
                return $filename_new . "_" . 1 . "." . $file->getExtension();
            }
        }
        return $filename;
    }

    public static function rename_file($rename_files)
    {
        foreach ($rename_files as $file) {
            rename($file->getRealPath(), dirname($file->getRealPath()) . DIRECTORY_SEPARATOR . $file->good_name);
        }
    }

    public static function rename_cyr($rename_files, $images_files, $html_files)
    {

        foreach ($rename_files as $file) {

            $sanited = Helper::sanitize($file->getBasename());
            $good_name = Helper::check_exist_name($sanited, $images_files);
            $file->good_name = $good_name;

        }
        Helper::rename_file($rename_files);
        Helper::rename_files_in_html($rename_files, $html_files);

    }

    public static function sanitize($string, $force_lowercase = true, $anal = false)
    {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ">", "/", "?");

        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;

        $cyrillicPattern = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у',
            'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ь', 'э', 'ы', 'ю', 'я', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У',
            'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ь', 'Э', 'Ы', 'Ю', 'Я');

        $latinPattern = array('a', 'b', 'v', 'g', 'd', 'e', 'jo', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u',
            'f', 'h', 'ts', 'ch', 'sh', 'sht', '', '\'', 'je', 'ji', 'yu', 'ya', 'A', 'B', 'V', 'G', 'D', 'E', 'Jo', 'Zh',
            'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U',
            'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', '', '\'', 'Je', 'Ji', 'Yu', 'Ya');

        $clean = str_replace($cyrillicPattern, $latinPattern, $clean);
        return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
        mb_strtolower($clean, 'UTF-8') :
        strtolower($clean) :
        $clean;
    }

}
