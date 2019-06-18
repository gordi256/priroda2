<?php

namespace App\Http\Controllers;

use App\Support\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoController extends Controller
{
    public function upload(Request $request)
    {
        $guid = (string) Str::uuid();

        $path_name = public_path() . DIRECTORY_SEPARATOR . $guid;
        $result = File::makeDirectory($path_name, 0777);
        $uploadedFile = $request->file('customFile');

        \Zipper::make($uploadedFile)->extractTo($path_name);

        $directories = Storage::allDirectories($path_name);
        $files = File::allFiles($path_name);

        foreach ($files as $file) {
            $is_image = Helper::check_image($file->getExtension());
            if ($is_image) {
                // if (exif_imagetype($file)  >0){  }
                $images_files[] = $file;
                $res = Helper::check_cyr($file->getFilename());
                if (Helper::check_cyr($file->getFilename())) {


                    $rename_files[] = $file;
                }
            } else {
                if ($file->getExtension() == "html") {
                    $html_files[] = $file;
                }
            }

        }

// todo кодировка (если не проходит проверку - $rename_files пуст)
        Helper::rename_cyr($rename_files, $images_files, @$html_files);
        \Zipper::make('result/test.zip')->add($path_name)->close();

          $result = File::deleteDirectory($path_name);
          return response()->download(public_path('result/test.zip'));
// убить файл'result/test.zip
        //  dd($path_name, $files, @$html_files, @$rename_files, @$images_files);

    }
}
