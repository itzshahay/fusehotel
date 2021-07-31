<?php
namespace App\Controllers;

use App\Config;
use App\Hash;
use App\Token;

use App\Models\Core;
use App\Models\Room;
use App\Models\Player;

use Library\HotelApi;
use Library\Json;

use Core\Session;
use Core\Locale;

class Api
{
    public $settings;

    public function __construct()
    {
        $this->settings = Core::settings();
    }
  
    public function ssoTicket()
    {
        if(!request()->player) {
            response()->json([
                'error' => 'Required login',
            ]);
        }
      
        $auth_ticket = Token::authTicket(request()->player->id);
        Player::update(request()->player->id, ["auth_ticket" => $auth_ticket]);
      
        if(!empty($auth_ticket)) {
            response()->json([
                "status"  => "success",  
                "ticket" => $auth_ticket
            ]);
        }
    }
  
    public function vote()
    {
          $FindRetros = new \Library\FindRetros();

          if($FindRetros->hasClientVoted()) {  
              $this->callback = ["status" => "voted"];
          } else {
              $this->callback = [
                  "status"  => 0,
                  "api"     => $FindRetros->redirectClient()
              ];
          }
      
          response()->json($this->callback ?? null);
    }
  
    public function room($roomId)
    {
        if (!request()->player->online) {
            response()->json([
                "status"      => "success",  
                "replacepage" => "hotel?room=" . $roomId
            ]);
        }

        $room = \App\Models\Room::getById($roomId);
        if ($room == null) {
            response()->json([
                "status" => "error", 
                "message" => Locale::get('core/notification/room_not_exists')
              ]);
        }

        HotelApi::execute("forwarduser", [
          "user_id" => request()->player->id, 
          "room_id" => $roomId
        ]);
      
        response()->json([
            "status" => "success",  
            "replacepage" => "hotel"
        ]);
    }

    public function user($username)
    {
        $user = Player::getDataByUsername($username);
        if(!$user) {
            response()->json([
                'error' => 'User not found'
            ]);
        }

        $response = [
            'username'  => $user->username,
            'motto'     => $user->motto,
            'credits'   => $user->credits,
            'look'      => $user->look
        ];

        
        foreach(Player::getCurrencys(request()->player->id) as $value) {
            $response += [$value->currency => $value->amount];
        }
      
        response()->json($response);
    }
  
    public function online()
    {
        echo Core::getOnlineCount();
    }
  
    public function currencys() 
    {
        response()->json(Core::getCurrencys());
    }
}
