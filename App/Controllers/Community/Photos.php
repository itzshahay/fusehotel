<?php
namespace App\Controllers\Community;

use App\Core;
use App\Models\Community;
use App\Models\Player;

use Core\Locale;
use Core\View;

use Library\Json;

use stdClass;

class Photos
{
    private $data;

    public function __construct()
    {
        $this->data = new stdClass();
    }

    public function like()
    {
        if(!request()->player->id) {
            response()->json(["status" => "error", "message" => Locale::get('core/notification/something_wrong')]);
        }
      
        if (Community::userAlreadylikePhoto(input('post'), request()->player->id)) {
            response()->json(["status" => "error", "message" => Locale::get('core/notification/already_liked')]);
        }

        Community::insertPhotoLike(input('post'), request()->player->id);
        response()->json(["status" => "success", "message" =>Locale::get('core/notification/liked')]);
    }

    public function more()
    {
        $this->index(input('offset'), true);
        response()->json(['photos' => $this->data->photos]);
    }

    public function index($offset = 0, $request = false)
    {
        if(is_array($offset)) {
            $photos = Community::getPhotos(12);
        } else {
            $photos = Community::getPhotos(12, $offset);
        }

        foreach($photos as $photo) {
            $user = Player::getDataById($photo->user_id, array('username','look'));

            $photo->author =  $user->username;
            $photo->figure =  $user->look;

            $photo->likes = Community::getPhotosLikes($photo->id);
        }

        $this->data->photos = $photos;

        if($request == false)
            View::renderTemplate('Community/photos.html', [
                'title' => Locale::get('core/title/community/photos'),
                'page' => 'community_photos',
                'data' => $photos
            ]);
    }
}