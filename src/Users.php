<?php

namespace Tiktok;

use Exception;

final class Users
{
  const URI_BASE = 'https://www.tiktok.com/';

  function __construct()
  {
    $this->object = [];

    $this->statusCode = '';
  }

  public function details($user)
  {
    if (empty($user)) {
      throw new Exception('Missing required argument: "user"');
    }

    $this->user = $user;

    $request = $this->request();

    $response = $this->extract(
      '/<script id="__NEXT_DATA__"([^>]+)>([^<]+)<\/script>/',
      $request
    );

    if ($this->statusCode) {
      return $this->template($response, $this->object, $this->statusCode, [
        'user' => [
          'id' => 'id',
          'username' => 'nickname',
          'profileName' => 'uniqueId',
          'avatar' => 'avatarMedium',
          'description' => 'signature',
          'verified' => 'verified',
        ],
        'stats' => [
          'following' => 'followingCount',
          'follower' => 'followerCount',
          'video' => 'videoCount',
          'like' => 'heartCount',
        ],
      ]);
    }
  }

  protected function request($method = 'GET', $getParams = [])
  {
    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL =>
        self::URI_BASE . '@' . $this->prepare($this->user) . '/?lang=ru',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => [
        'user-agent:  Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.101 Safari/537.36',
      ],
    ]);

    $response = curl_exec($curl);

    $this->statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    return $response;
  }

  private function prepare($user)
  {
    $value = $user;

    if ($value) {
      return strtolower(preg_replace('/@/', '', $value, 1));
    }
  }

  private function extract($pattern, $_)
  {
    preg_match($pattern, $_, $matches);

    return json_decode($matches[2], 1)['props']['pageProps']['userInfo'];
  }

  private function template($request_, $object_, $statusCode_, $template_ = [])
  {
    $object_['code'] = $statusCode_;

    switch ($statusCode_) {
      case 200:
        foreach ($template_ as $userInfoKey => $value) {
          foreach ($value as $key => $values) {
            $object_[$userInfoKey][$key] = $request_[$userInfoKey][$values];
          }
        }
        break;

      case 404:
        $object_['error'] = 'This account cannot be found.';

        break;
      default:
        $object_['error'] = 'The page cannot load.';
        break;
    }

    return json_encode($object_);
  }
}
