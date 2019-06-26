<?php
/**
 * Created by PhpStorm.
 * User: Faizan
 * Date: 2019-02-11
 * Time: 16:18
 */

namespace App\Libraries;
use App\Libraries\TBO\TBOSOAPClient;
use AvailabilityAndPricingRequest;
use HotelBookRequest;
use HotelDetailsRequest;
use HotelRoomAvailabilityRequest;
use HotelSearchRequest;
use RoomGuest;

class TboLibrary
{

    function __construct($modules,$request)
    {
        $client = new TBOSOAPClient();

        if($modules->is_own_credentials)
        {
            $this->tbo = $client->createClient($request->credentials);

        }else{
            $this->tbo = $client->createClient("");
        }
        $date = trim(gmdate('U'));
    }

    function list($params)
    {

        $CheckInDate = new \DateTime(date('Y-m-d', strtotime($params["checkin"])));
        $CheckOutDate = new \DateTime(date('Y-m-d', strtotime($params["checkout"])));
        $CountryName = "";
        $CityName = "";
        $CityId = 25921;
        $IsNearBySearchAllowed = true;
        $NoOfRooms = 1;
        $GuestNationality = "PK";
        $RoomGuests = array();
        for ($i = 0; $i < $NoOfRooms; $i++) {
            array_push($RoomGuests, new RoomGuest($params["adults"], $params["children"]));
        }
        $PreferredCurrencyCode = "PKR";
        $ResultCount = 100;
        $HotelSearchRequest = new HotelSearchRequest($CheckInDate, $CheckOutDate, $CountryName, $CityName, $CityId, $IsNearBySearchAllowed, $NoOfRooms, $GuestNationality, $RoomGuests, $PreferredCurrencyCode, $ResultCount, null);
        $searchResponse = $this->tbo->HotelSearch($HotelSearchRequest);
        $return_response = array();
        if (!empty($searchResponse->Status->StatusCode) && $searchResponse->Status->StatusCode == "01") {
            foreach ($searchResponse->HotelResultList->HotelResult as $item) {
                array_push($return_response, array(
                    "id" => $item->HotelInfo->HotelCode,
                    "company_name" => $item->HotelInfo->HotelName,
                    "hotel_domain" => "",
                    "address" => $item->HotelInfo->HotelAddress,
                    "description" => $item->HotelInfo->HotelDescription,
                    "hotels_status" => 0,
                    "city_id" => $searchResponse->CityId,
                    "state_id" => "",
                    "country_id" => 0,
                    "status" => "",
                    "commission_percentage" => 0,
                    "supplier_id" => "",
                    "latitude" => $item->HotelInfo->Latitude,
                    "longitude" => $item->HotelInfo->Longitude,
                    "rating" => $item->HotelInfo->Rating,
                    "created_at" => "",
                    "updated_at" => "",
                    "subdomain" => "",
                    "checkin" => $searchResponse->CheckInDate,
                    "checkout" => $searchResponse->CheckOutDate,
                    "account_id" => "",
                    "ota_id" => "",
                    "feature_city_id" => "",
                    "hotel_slug" => $item->HotelInfo->HotelCode,
                    "mobile_number" => "",
                    "phone_number" => "",
                    "whatsapp_number" => "",
                    "currencies" => $item->MinHotelPrice->Currency,
                    "prefer_currency" => $item->MinHotelPrice->PrefCurrency,
                    "email_address" => "",
                    "city_name" => $searchResponse->CityId,
                    "price" => $item->MinHotelPrice->TotalPrice,
                    "prefer_price" => $item->MinHotelPrice->PrefPrice,
                    "image" => $item->HotelInfo->HotelPicture,
                    "type" => "",
                    'rooms' => "",
                    'api_name' => 4,
                    "custom_payload" => (object)[
                        "session_id" => $searchResponse->SessionId,
                        "ResultIndex" => $item->ResultIndex,
                        "vendor"=>7
                    ],
                ));
            }
            return array('status' => "success", "code" => 200, 'data' => $return_response);
        } else {
            return array('status' => "error", "code" => 401, 'data' => $searchResponse->Status->Description);
        }
    }

    function details($params)
    {
        $hotels_details = new HotelDetailsRequest(json_decode($params["custom_payload"])->ResultIndex,json_decode($params["custom_payload"])->session_id,$params["hotel_id"]);
        $hotels_details_response = $this->tbo->HotelDetails($hotels_details);
        if (!empty($hotels_details_response->Status->StatusCode) && $hotels_details_response->Status->StatusCode == "01") {
            $hotels_details_object =  $hotels_details_response->HotelDetails;

            if($hotels_details_object->HotelRating == "OneStar")
            {
                $rating = 1;
            }else if($hotels_details_object->HotelRating == "TwoStar") {
                $rating = 2;
            }else if($hotels_details_object->HotelRating == "ThreeStar") {
                $rating = 3;
            }else if($hotels_details_object->HotelRating == "FourStar") {
                $rating = 4;
            }else if($hotels_details_object->HotelRating == "FiveStar") {
                $rating = 5;
            }else if($hotels_details_object->HotelRating == "SixStar") {
                $rating = 6;
            }
             $hotel_images = [];
            foreach ($hotels_details_object->ImageUrls->ImageUrl as $item)
            {
                array_push($hotel_images,$item->_);
            }
                $hotels_list = array(
                "id" => $hotels_details_object->HotelCode,
                "hotel_order" => "",
                "hotel_is_featured" => "",
                "slider_image" => empty($hotel_images[0]) ? "" : $hotel_images[0],
                "company_name" => $hotels_details_object->HotelName,
                "hotel_domain" => "",
                "address" => $hotels_details_object->Address,
                "description" => $hotels_details_object->Description,
                "hotels_status" => 0,
                "city_id" => "",
                "status" => 1,
                "commission_percentage" => 0,
                "supplier_id" => "",
                "latitude" => explode("|",$hotels_details_object->Map)[0],
                "longitude" => explode("|",$hotels_details_object->Map)[1],
                "rating" => $rating,
                "created_at" => "",
                "updated_at" => "",
                "subdomain" => "",
                "checkin" => "",
                "account_id" => "",
                "ota_id" => "",
                "feature_city_id" => "",
                "hotel_slug" => "",
                "mobile_number" => "",
                "phone_number" => '',
                "whatsapp_number" => "",
                "email_address" => "",
                "thumb" => empty($hotel_images[0]) ? "" : $hotel_images[0],
                "hotel_policy" => "",
                "property_type_id" => "",
                "currency_name" => "",
                "rooms" => $this->roomAvailibility($params),
                'images' => $hotel_images,
                "amenities" => $hotels_details_object->HotelFacilities->HotelFacility,
                "custom_payload"=>json_decode($params["custom_payload"])
            );

            return array('status' => "success", "code" => 200, 'hotels' => $hotels_list);
        } else {
            return array('status' => "error", "code" => 401, 'data' => $hotels_details_response->Status->Description);
        }
    }

    function roomAvailibility($params)
    {
        $client = new TBOSOAPClient();
        $tbo = $this->client->createClient();
        $hotel_room_avilibility_request = new HotelRoomAvailabilityRequest(json_decode($params["custom_payload"])->session_id, json_decode($params["custom_payload"])->ResultIndex, $params["hotel_id"]);
        $data = $this->tbo->AvailableHotelRooms($hotel_room_avilibility_request);

        if (!empty($data->Status->StatusCode) && $data->Status->StatusCode == "01") {
            $rooms = [];

            foreach ($data->HotelRooms->HotelRoom as $room) {
                $images = array();

                $rooms[] = array(
                    'room_status' => '',
                    'refundable' => '',
                    'id' => $room->RoomIndex,
                    'type_id' => '',
                    'price' => $room->RoomRate->TotalFare,
                    'hotel_id' => $params['hotel_id'],
                    'room_descriptions' => "",
                    'image' => $images,
                    'room_name' => $room->RoomTypeName,
                    'room_slug' => $room->RoomTypeName,
                    'supplier_id' => '',
                    'adults' => "",
                    'childs' => "",
                    'type' => "",
                    'images' => $images
                );
            }

            return $rooms;
        } else {
            return array();
        }
    }
    function save_booking($params)
    {

        $room_aviliabilty = new AvailabilityAndPricingRequest(
            json_decode($params["custom_payload"])->ResultIndex,
            json_decode($params["custom_payload"])->session_id,
            $params["hotel_id"],
            array(
                    "FixedFormat" => "true",
                    "RoomCombination" => array(
                        "RoomIndex"=>1
                    )
            ));
        $room_booking = new HotelBookRequest();


    }
}
