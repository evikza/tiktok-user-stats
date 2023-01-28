<?php

namespace Tiktok;

use Exception;

final class Users
{
  const URI_BASE = 'https://www.tiktok.com/';

  private $object;
  private $user;
  private $statusCode;

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

    $this->user = $this->prepare($user);

    $request = $this->request();

    $response = $this->extract(
      '/<script id="SIGI_STATE"([^>]+)>([^<]+)<\/script>/',
      $request
    );

    if ($this->statusCode) {
      return $this->template(
        $response,
        'UserModule',
        $this->object,
        $this->statusCode,
        [
          'users' => [
            'id' => 'id',
            'username' => 'nickname',
            'profileName' => 'uniqueId',
            'avatar' => 'avatarMedium',
            'description' => 'signature',
            'region' => 'region',
            'verified' => 'verified',
          ],
          'stats' => [
            'following' => 'followingCount',
            'follower' => 'followerCount',
            'video' => 'videoCount',
            'like' => 'heartCount',
          ],
        ]
      );
    }
  }

  protected function request($method = 'GET', $getParams = [])
  {
    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL => self::URI_BASE . '@' . $this->user . '/?lang=ru',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => [
        'cookie: msToken=R45dciBnhPoA1Bz69EOS1LE9UDiEhQouwqJV8X6UIwqvcfazJ0fo77aw7XZc6BX1b7mwC23AQzArbKkMKRIRvacXW12yCGU4HY55',
        'user-agent: Mozilla/5.0 (compatible; Google-Apps-Script)',
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

    return json_decode($matches[2], 1);
  }

  private function template(
    $request_,
    $requestModule,
    $object_,
    $statusCode_,
    $template_ = []
  ) {
    $object_['code'] = $statusCode_;

    switch ($statusCode_) {
      case 200:
        foreach ($template_ as $userInfoKey => $value) {
          foreach ($value as $key => $values) {
            $object_[str_replace('users', 'user', $userInfoKey)][$key] =
              $request_[$requestModule][$userInfoKey][$this->user][$values];
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
