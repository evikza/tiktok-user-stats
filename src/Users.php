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
        'cookie: odin_tt=32d9d9a534736f41356a200d4620150b7bf8fbc5aa6048557fdd09da6d00576b9e0aa9c6f11ad5e5855ef29e98c39cfd29d72c1ac6054274080bac1b559e2fbb; _abck=F90BC26C136E46560858A636399DF67F~-1~YAAQBksrvJsa4sGFAQAAwj3C2wkDu4/RnWn/+p+IksNEWklLCJBGCyncpivAQEkvXUmcZJvxRhyixBBOWEs7Cj6wMvZAGNfeG4tGq9cyLvNXlBR4GFPbbSPDtdBeRa6wBCDOTWPgZvqxWFsbThkLwqv9IdslHRlVhzYln+TidZhjdrc5tF3fVvlpZL3EusUP4qxA9UZVMx/veloRoS4l8rpuNb8ToAojR26LFV+N7Au4NwbDt70iyFWgyYnPAUkrqWIbq0w2O2InE+w+JyorrD3U24+gBxve+an2agciHWifNgon0zISzma2bsn9nwgQc+2R71nMrBqcjxNS0GueD+YbF1G6Fe2mOhDts3FoKdZM0djkwOMneWA=~-1~-1~-1; bm_sz=7522C93E4364541FDCECA8FE675A80BF~YAAQBksrvJwa4sGFAQAAwj3C2xKiwwSHJMG9/eyRm5b7Oz6wNeTNJEsx3MMTHWYBOS4pTe+w50pVZqeoqPC5o+Soq0dLsQgb8Xq5Ch5W3xQQoxOoSNC93S7uLArkn/MkrFSRMblNYj6iRW1lbIenPeshsHS8Zp8AmsmZbnVcrT6GXzhbJOuBKUpVaae73Bt6H5IuCR/a9Dlzro7TgY9Whjyh2sy17tj6DBXAx2DWC/IZC7kxYmijtbFiT7Ll/RswyOzY7kBPwOp1ro1byaoN/qcbIoMBFVxbHkAQEQBkmFZRlm0=~3228978~3355971; msToken=R45dciBnhPoA1Bz69EOS1LE9UDiEhQouwqJV8X6UIwqvcfazJ0fo77aw7XZc6BX1b7mwC23AQzArbKkMKRIRvacXW12yCGU4HY55; tt_chain_token=Ud90lS3pZTvCRu1rfXlz6w==; tt_csrf_token=cOWKCbaB-GibxvJvVwp0zV7wNOvtFDPgYtMU; ttwid=1%7C_QLHExwzpZyWtfWyIEKD93V2OU5YHbYRJvoeokQAdw4%7C1674430218%7C9f85ee510f4681a0f3672fbc1bd3ce29609b9c89173a40d31aa70daeb02754cd; s_v_web_id=verify_ld7z8t9e_drP6Sr4G_Uj4A_4p7y_BOWW_DiWccDvExowV',
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
