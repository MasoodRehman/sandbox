<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 1/30/2019
 * Time: 4:51 PM
 */

namespace App\Libraries;


use Illuminate\Support\Facades\DB;

class TourPackage
{
    public static function getSearchFormData($slug, $ota_id, $packageDate, $adults, $child)
    {
        $textualDay = date('D', strtotime($packageDate));
        $default_thumbnail = config('constant.image_upload_url').'default.png';
        $dir_path_supplier = config('constant.image_upload_url').$ota_id."/supplier/";

        $ota_featured_packages = DB::table('package_tours')
            ->select('package_tours.*', 'package_locations.name as location', DB::Raw(
                "
                    IFNULL(CONCAT(
                        '{$dir_path_supplier}',
                        package_tours.supplier_id, 
                        '/thumbs/', 
                        package_tour_images.image
                    ), '{$default_thumbnail}') as thumbnail
                "
            ), 'flag_thumbnail')
            ->join('package_locations', 'package_locations.id', '=', 'package_tours.location_id')
            ->leftJoin('package_tour_images', 'package_tour_images.package_tour_id', '=', 'package_tours.package_tour_id')
            ->whereRaw(
                "CASE 
                    WHEN package_tours.type_key = 'every_day' THEN TRUE
                    WHEN package_tours.type_key = 'weekdays' THEN find_in_set('{$textualDay}', package_tours.type_value) <> 0
                    WHEN package_tours.type_key = 'specific_dates' THEN find_in_set('{$packageDate}', package_tours.type_value) <> 0
                    ELSE FALSE 
                END"
            )
            ->where('package_locations.slug', $slug)
            ->where('package_tours.maxadult', $adults)
            ->where('package_tours.maxchild', $child)
            ->having('flag_thumbnail' , '=', 1)
            ->orHavingRaw('flag_thumbnail is null')
            ->get();

        return $ota_featured_packages;
    }

    public static function getByOtaId($ota_id)
    {
        $default_thumbnail = config('constant.image_upload_url').'default.png';
        $dir_path_supplier = config('constant.image_upload_url').$ota_id."/supplier/";
        $ota_featured_packages = DB::table('package_tours')
            ->select('package_tours.*', 'package_locations.name as location', DB::Raw("
                    IFNULL(CONCAT(
                        '{$dir_path_supplier}',
                        package_tours.supplier_id, 
                        '/thumbs/', 
                        package_tour_images.image
                    ), '{$default_thumbnail}') as thumbnail
                "), 'flag_thumbnail')
            ->join('package_locations', 'package_locations.id', '=', 'package_tours.location_id')
            ->leftJoin('package_tour_images', 'package_tour_images.package_tour_id', '=', 'package_tours.package_tour_id')
            ->where('is_featured', 'Yes')
            ->where('package_tours.ota_id', '=', $ota_id)
            ->having('flag_thumbnail' , '=', 1)
            ->orHavingRaw('flag_thumbnail is null')
            ->get();

        return $ota_featured_packages;
    }
}