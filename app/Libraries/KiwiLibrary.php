<?php

namespace App\Libraries;

use App\Account;
use App\Currency;
use App\Guest;
use App\KiwiFlight;
use App\KiwiPassenger;
use App\Mail\MailClass;
use App\Ota;
use App\SuperBooking;
use App\test_flight_search;
use http\Env\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class KiwiLibrary {

	function __construct( $credentials = "", $modules )
    {

	    $this->modules = $modules[0];

        if ( $modules[0]->test == 1 ) {
            $this->env       = 'test';
			$this->mode      = "sandbox";
			$this->partner   = 'phptravels';
			$this->sandbox   = 1;
			$this->programId = 'PAPI_ZooZNP_OYMISN3HZ6ZJFL6LSYV4WAYLOQ_5';
		} else {
            $this->env       = 'live';
            $this->sandbox   = 0;
			$this->mode      = "production";
			$this->partner   = 'phptravels';
			$this->programId = 'PAPI_ZooZNP_DN3MVFFPPITAVM4Z5LJUYE6KTQ_70';
		}
	}

	function ajax_call( $url, $params, $headers) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		if(!empty($headers)){
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		}
		if(!empty($params)){
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
		}
		$content = curl_exec( $ch );
		return $content;
	}

	function flight_search( $params = array() ) {
		$headers           = array(
			'Content-Type: application/json'
		);
		$params_send = array(
		    "flyFrom" => $params["from_code"],
		    "to" => $params["to_code"],
		    "dateFrom" => $params["date_from"],
		    "adults" => $params["adults"],
		    "children" => $params["children"],
		    "infants" => $params["infants"],
		    "dateTo" => $params["date_from"],
		    "curr" => $params["currency"],
        );
		if($params["flight_type"] == "return")
        {
            $params_send['return_from'] = $params["return_from"];
            $params_send['return_to']   =  $params["return_from"];
            $params_send["flight_type"] = "return";
        }
		$params_send["partner"] = $this->partner;
        $currency = Currency::where('id',$this->modules->currency_id)->first();
        $currency_convert = Currency::where('name',$params["currency"])->first();
        $params_send["curr"] = $currency->name;
		$content = $this->ajax_call( "https://api.skypicker.com/flights?" . http_build_query( $params_send ), "", $headers );
		$flights    = json_decode( $content, true );
		$all_flight = array();
		if ( ! empty( $flights['data'] ) && empty( $flights['message'] ) ) {
			foreach ( $flights['data'] as $flight ) {

				$stops_return = 0;
				$stops        = 0;
				foreach ( $flight['route'] as $route ) {
					if ( $route['return'] == 1 ) {
						$stops_return ++;
					} else {
						$airline = $flight['airlines'][0];
						$stops ++;
					}
				}
				$return_departure         = "";
				$return_departure_airline = "";
				$return_arrival           = "";
				foreach ( $flight['route'] as $route ) {
					if ( $route['return'] == 1 ) {
						for ( $i = 0; $i < $stops_return; $i ++ ) {
							if ( $i == 0 && empty( $return_departure ) ) {
								$return_departure         = $route['dTime'];
								$return_departure_airline = $route['airline'];
							}
							if ( $i == $stops_return - 1 ) {
								$return_arrival = $route['aTime'];
							}
						}
					}
				}
				$unique_id    = uniqid();
				$total_price = "";
				if($this->modules->is_markup)
                {
                    if($this->modules->markup_percentage != 0)
                    {
                        $flight_price = ($flight['price']/100) * $this->modules->markup_percentage;
                    }
                    $total_price = $flight['price'] + $flight_price +  $this->modules->markup_fixed;
                }
                if($this->modules->is_markup_ota)
                {
                    if($this->modules->markup_percentage_ota != 0)
                    {
                        $flight_price = $total_price * $this->modules->markup_percentage;
                    }
                    $flight['price'] = $total_price + $flight_price +  $this->modules->markup_fixed;
                }
                $flight['price'] = Helpers::currency_converter($currency->rate, $currency_convert->rate, $flight['price']);
                foreach ($flight["route"] as &$item)
                {
                    $item = (object)array(
                        "city_from"=>$item["cityFrom"],
                        "city_to"=>$item["cityTo"],
                        "from_code"=>$item["flyFrom"],
                        "to_code"=>$item["flyTo"],
                        "airline"=>$item["airline"],
                        "bags_recheck_required"=>$item["bags_recheck_required"],
                        "return"=>$item["return"],
                        "latTo"=>$item["latTo"],
                        "flight_no"=>$item["flight_no"],
                        "price"=>$item["price"],
                        "original_return"=>$item["original_return"],
                        "map_from"=>$item["mapIdfrom"],
                        "airline_type"=>$item["equipment"],
                        "map_to"=>$item["mapIdto"],
                        "latitude_from"=>$item["latFrom"],
                        "longitude_from"=>$item["lngFrom"],
                        "longitude_to"=>$item["lngTo"],
                        "latitude_to"=>$item["latTo"],
                        "fare_basis"=>$item["fare_basis"],
                        "guarantee"=>$item["guarantee"],
                        "fare_classes"=>$item["fare_classes"],
                        "arrival_utc_time"=>$item["aTimeUTC"],
                        "departure_utc_time"=>$item["dTimeUTC"],
                        "arrival_time"=>$item["aTime"],
                        "departure_time"=>$item["dTime"],
                        "vehicle_type"=>$item["vehicle_type"],
                    );
                }
//              $check =   test_flight_search::create($array_inset);
                $all_flight[] = array(
                    'from_code'                     => $flight['flyFrom'],
                    'to_code'                       => $flight['flyTo'],
                    'airline'                  => $airline,
                    'currency'                 => $currency_convert->name,
                    'flight_price'             => ceil($flight['price']),
                    'flight_duration'          => $flight['fly_duration'],
                    'stops'                    => $stops,
					'departure_time'           => $flight['dTime'],
					'arrival_time'             => $flight['aTime'],
					'flight_id'                => $flight['id'],
					'baglimit'                 => array(
						'hand_width'  => ( ! empty( $flight['baglimit']['hand_width'] ) ) ? $flight['baglimit']['hand_width'] : "",
						'hand_length' => ( ! empty( $flight['baglimit']['hand_length'] ) ) ? $flight['baglimit']['hand_length'] : "",
						'hand_weight' => ( ! empty( $flight['baglimit']['hand_weight'] ) ) ? $flight['baglimit']['hand_weight'] : "",
						'hand_height' => ( ! empty( $flight['baglimit']['hand_height'] ) ) ? $flight['baglimit']['hand_height'] : "",
						'hold_weight' => ( ! empty( $flight['baglimit']['hold_weight'] ) ) ? $flight['baglimit']['hold_weight'] : "",
					),
					'return_departure'         => ( ! empty( $return_departure ) ) ? $return_departure : "",
					'return_departure_airline' => ( ! empty( $return_departure_airline ) ) ? $return_departure_airline : "",
					'return_arrival'           => ( ! empty( $return_arrival ) ) ? $return_arrival : "",
					'bags_price'               => $flight['bags_price'],
					'routes'                   => $flight['routes'],
					'route'                    => $flight['route'],
					'custom_payload'           => (object) [
						'visitor_uniqid' => $unique_id,
						'booking_token'  => $flight['booking_token']
					]
				);
			}
			return $all_flight;
		} else {
			return $all_flight;
		}
	}

	function booking( $params = array() ) {
		$headers = array(
			'Content-Type: application/json'
		);
		$content = $this->ajax_call( "https://booking-api.skypicker.com/api/v0.1/check_flights?v=2&" . http_build_query( $params ), "", $headers );
		$content         = json_decode( $content, true );
		$content['mode'] = $this->mode;
		$flights         = [];

		if ( ! empty( $content['flights'] ) ) {
			foreach ( $content['flights'] as $flight ) {

                $flights[] = array(
                    'flight_id'         => $flight['id'],
                    'flight_no'         => $flight['flight_no'],
                    'operating_airline' => $flight['operating_airline'],

                    'from_country' => $flight['src_country'],
                    'from_code'         => $flight['src'],
                    'from_city'    => $flight['src_name'],
                    'from_station' => $flight['src_station'],

                    'to_city'    => $flight['dst_name'],
                    'to_country' => $flight['dst_country'],
                    'to_code'         => $flight['dst'],
                    'to_station' => $flight['dst_station'],

                    'arrival_utc_time'   => $flight['atime_utc'],
                    'departure_utc_time'   => $flight['dtime_utc'],
                    'arrival_time'       => $flight['atime'],
                    'departure_time'       => $flight['dtime'],
                    'return' => $flight['return']
                );
			}

			$data = array(
				'flights_checked' => $content['flights_checked'],
				'flights_invalid' => $content['flights_invalid'],
				'currency'        => $content['currency'],
				'total'           => $content['total'],
				'book_fee'        => $content['book_fee'],
				'flights'         => $flights,
				'custom_payload'  => array(
					'booking_token'  => $content['booking_token'],
					'visitor_uniqid' => $params['visitor_uniqid'],
				),
				'route'           => $content['route'],
				'mode'            => $this->mode
			);
		} else {
			$data = $content;
		}

		return $data;
	}

	function response_return( $status, $booking, $initpayment, $confirmpayment, $error = "", $booking_id = "" ) {
		return json_encode( array(
				'status'         => $status,
				'booking'        => $booking,
				'initpayment'    => $initpayment,
				'confirmpayment' => $confirmpayment,
				"error"          => $error,
				"booking_id"     => $booking_id
			)
		);
	}

	function save_booking( $params = array() ) {
		$return = [];
		$post   = $params;

		if ( count( $post['account'] ) < 6 ) {
			return $this->response_return( false, false, false, false, "Missing Account Parameters" );
		}

		if (
			empty( $post['account']['title'] ) ||
			empty( $post['account']['first_name'] ) ||
			empty( $post['account']['last_name'] ) ||
			empty( $post['account']['email'] ) ||
			empty( $post['account']['mobile_code'] ) ||
			empty( $post['account']['number'] )
		) {
			return $this->response_return( false, false, false, false, false, "Missing Account Parameters" );
		}

		if ( empty( $post['custom_payload']['booking_token'] ) || empty( $post['custom_payload']['visitor_uniqid'] ) ) {
			return $this->response_return( false, false, false, false, false, "Missing Custom Payload Parameters" );
		}

		if ( empty( $post['payment_method'] ) || $post['payment_method'] == "pay_now" ) {
			if (
				empty( $post['payment_details']['name_card'] ) ||
				empty( $post['payment_details']['card_no'] ) ||
				empty( $post['payment_details']['month'] ) ||
				empty( $post['payment_details']['year'] ) ||
				empty( $post['payment_details']['security_code'] )
			) {
				return $this->response_return( false, false, false, false, false, "Missing Payment Information" );
			}
		}


		$flight_ids = explode( "|", $post->flight_id );
		// --------------- Passengers Information ----------  //
		$total_bags = 0;
		$passengers = [];
		if ( ! empty( $post['adults'] ) ) {
			if ( empty( $post['adults'][0]['title'] ) ) {
				return $this->response_return( false, false, false, false, false, "Adult Information Must be of dict type" );
			} else {
				foreach ( $post['adults'] as $key => $value ) {
					$value['birthday']   = strtotime( $value['birthday'] );
					$value['expiration'] = strtotime( $value['expiration'] );
					$hold_bag_flights    = [];
					$total_bags ++;
					foreach ( $flight_ids as $flight_id ) {
						$hold_bag_flights[ $flight_id ] = array( "1" => 1, "2" => 0, "3" => 0 );
					}
					$value['hold_bags'] = $hold_bag_flights;
					$passengers[]       = $value;
				}
			}
		} else {
			return $this->response_return( false, false, false, false, "Missing Adult Information" );
		}

		if ( ! empty( $post['children'] ) ) {
			foreach ( $post['children'] as $key => $value ) {
				$value['birthday']   = strtotime( $value['birthday'] );
				$value['expiration'] = strtotime( $value['expiration'] );
				$hold_bag_flights    = [];
				$total_bags ++;
				foreach ( $flight_ids as $flight_id ) {
					$hold_bag_flights[ $flight_id ] = array( "1" => 1, "2" => 0, "3" => 0 );
				}
				$value['hold_bags'] = $hold_bag_flights;
				$passengers[]       = $value;
			}
		}

		if ( ! empty( $post['infants'] ) ) {
			foreach ( $post['infants'] as $key => $value ) {
				$value['birthday']   = strtotime( $value['birthday'] );
				$value['expiration'] = strtotime( $value['expiration'] );
				$hold_bag_flights    = [];
				$total_bags ++;
				foreach ( $flight_ids as $flight_id ) {
					$hold_bag_flights[ $flight_id ] = array( "1" => 1, "2" => 0, "3" => 0 );
				}
				$value['hold_bags'] = $hold_bag_flights;
				$passengers[]       = $value;
			}
		}

		// ------------- End - Passengers Information ------------ //

		// ------------- Send date to Api to Insert Booking into Database -------- //

		$account                      = $post["account"];
		$account['user_login_status'] = "guest";
		$account['account_id']        = 0;
        info('post account: ' . json_encode($account));
		$account_data = Account::where( 'email', $account["email"] )->first();
		info('db account: ' . json_encode($account_data));
		if ( empty( $account_data ) ) {
			$oAccount                  = new Account();
            $oAccount->first_name      = $account["first_name"];
            $oAccount->last_name       = $account["last_name"];
            $oAccount->email           = $account["email"];
            $oAccount->mobile_number   = $account["number"];
            $oAccount->account_type_id = 3;
            $oAccount->password        = 0;
            info('account to save: ' . json_encode($account));
            $oAccount->save();
		}

		$guest                = new Guest();
		$guest->first_name    = $account["first_name"];
		$guest->last_name     = $account["last_name"];
		$guest->mobile_number = $account["number"];
		$guest->account_id    = $account['account_id'];
		$guest->ota_id        = $post['ota_id   '];
		$guest->save();

		$super                 = new SuperBooking();
		$super->account_id     = $account['account_id'];
		$super->model_id       = 2;
		$super->model_type_id  = 1;
		$super->payment_method = "pay_now";
		$super->ota_id         = $post['ota_id'];
		$super->ip_address     = $post['ip_address'];
		$super->payment_method = $post['payment_method'];
		$super->booking_status = $post['booking_status'];
		$super->payment_status = $post['payment_status'];
		$super->device_type    = $post['device_type'];
		$super->save();

		$passengers_request = $passengers;
		foreach ( $passengers_request as $pr ) {
			$pr["hold_bags"]  = json_encode( $pr["hold_bags"] );
			$pr["booking_id"] = $super->id;
			$kiwiflight       = KiwiPassenger::create( $pr );
			$kiwiflight->save();
		}

		$flight_request = $post["flight_data"];
		foreach ( $flight_request as $flight ) {
			$flight["booking_id"] = $super->id;
			$kiwiflight           = KiwiFlight::create( $flight );
			$kiwiflight->save();
		}

		if ( $post['payment_method'] == 'pay_later' ) {
			$ota = Ota::where( "ota_id", $post['ota_id'] )->first();

			if ( empty( $ota->logo ) ) {
				$logo_url = config( "constant.image_upload_url" ) . "logo.png";
			} else {
				$logo_url = config( "constant.image_upload_url" ) . $ota->ota_id . "/main/" . $ota->logo;
			}
			if ( empty( $ota->favicon ) ) {
				$favicon_url = config( "constant.image_upload_url" ) . "favicon.png";
			} else {
				$favicon_url = config( "constant.image_upload_url" ) . $ota->ota_id . "/main/" . $ota->favicon;
			}

			$domain                    = DB::table( 'domains' )->where( 'account_id', $post['ota_id'] )->first();
			$objEmail                  = new \stdClass();
			$objEmail->app_url         = $post['url'];
			$objEmail->first_name      = $account['first_name'];
			$objEmail->last_name       = $account['last_name'];
			$objEmail->appname         = $ota->business_name;
			$objEmail->business_slogan = $ota->business_slogan;
			$objEmail->payment_method  = "pay_later";
			$objEmail->flight_data     = $post["flight_data"];
			$objEmail->invoice_code    = $super->id;
			$objEmail->invoice_url     = $domain->uri . "://" . $domain->domain . '/flights/invoice/' . $super->id;
			$objEmail->subject         = "Flight Booking";
			$objEmail->logo            = $logo_url;
			$objEmail->icon            = $favicon_url;
			$objEmail->view            = "emails.message.flight_invoice";
			Mail::to( $account['email'] )->send( new MailClass( $objEmail ) );
		}

		if ( $post['payment_method'] == "pay_online" ) {
			$passengers[0]['email'] = 'alerts@travelhope.com';
			$params_api             = json_encode( array(
					'lang'                   => "en",
					'bags'                   => 1,
					'passengers'             => $passengers,
					'locale'                 => "en",
					'booking_token'          => $post['custom_payload']['booking_token'],
					"immediate_confirmation" => false,
					'payment_gateway'        => 'payu',
					"partner"                => $this->partner,
					"booked_at"              => $this->partner,
//                    "currency"               => "usd",
//                    "affily"                 => $this->partner,
//                    "user_id"                => "test",
//                    "secret_token"           => "test"
				)
			);
            info('kiwi_booking_payload: ' . $params_api);
			$headers = array( 'Content-Type:application/json' );
			$content = $this->ajax_call( 'https://booking-api.skypicker.com/api/v0.1/save_booking',$params_api,$headers);
			info('kiwi_booking_response: ' . json_encode($content));
			$content_data = json_decode( $content, true );
			if ( ! empty( $content_data['payu_public_key'] ) ) {

				$payu_public_key = $content_data['payu_public_key'];

				$array_payment = array(
					"token_type"      => "credit_card",
					"credit_card_cvv" => $post['payment_details']['security_code'],
					"card_number"     => $post['payment_details']['card_no'],
					"expiration_date" => $post['payment_details']['month'] . "-" . $post['payment_details']['year'],
					"holder_name"     => $post['payment_details']['name_card']
				);

				$headers = array(
                    "Content-Type: application/json",
                    "api-version: 1.2.0",
                    "cache-control: no-cache",
                    "public-key: " . $payu_public_key,
                    "x-payments-os-env: " . $this->env
                );
				$curl = curl_init();
				curl_setopt_array( $curl, array(
					CURLOPT_URL            => "https://api.paymentsos.com/tokens",
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING       => "",
					CURLOPT_MAXREDIRS      => 10,
					CURLOPT_TIMEOUT        => 30,
					CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST  => "POST",
					CURLOPT_POSTFIELDS     => json_encode( $array_payment ),
					CURLOPT_HTTPHEADER     => $headers,
				) );

				$response = curl_exec( $curl );
                info('paymentsos_tokens_request: ' . json_encode($headers));
                info('paymentsos_tokens: ' . $response);

				$tokenize_data = json_decode( $response, true );
				if ( ! empty( $tokenize_data['token'] ) ) {
					$headers_payment_confirm = array(
					    'Content-Type:application/json',
                        'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
                    );
					$params_payment_confirm  = array(
						'booking_id'         => $content_data['booking_id'],
						'paymentToken'       => $content_data['payu_token'],
						'paymentMethodToken' => $tokenize_data['token'],
						'paymentCvv'         => $tokenize_data['encrypted_cvv'],
						'sandbox'            => $this->sandbox,
					);
                    info('paymentsos_confirm_payment_req: ' . json_encode($params_payment_confirm));
					$content_payment_confirm = $this->ajax_call( 'https://booking-api.skypicker.com/api/v0.1/confirm_payment',json_encode( $params_payment_confirm ),$headers_payment_confirm);
                    info('paymentsos_confirm_payment_res: ' . $content_payment_confirm);
					$content_payment_confirm = json_decode( $content_payment_confirm, true );
					if ( $content_payment_confirm['status'] == 0 ) {
						return $this->response_return( true, true, true, true, "", $super->id );
					} else {
						return $this->response_return( false, true, true, false, $content_payment_confirm );
					}
				} else {
					return $this->response_return( false, true, false, false, $tokenize_data );
				}
			} else {
				return $this->response_return( false, false, false, false, $content_data );
			}
		} else {
			return $this->response_return( true, false, false, false, "", $super->id );
		}
	}
}