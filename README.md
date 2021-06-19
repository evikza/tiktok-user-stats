# tiktok-users-stats
A dead-simple PHP package gives TikTok users statistics


### Usage

```php
<?php

// require __DIR__ . '/src/Users.php';

$getTiktokUser = new TikTok\Users();

echo $getTiktokUser->details('@evikza'); // or evikza
```

### Example responses 

###### HTTP_CODE 200

```json
{
  "code": 200,
  "user": {
    "id": "6827579470812759045",
    "username": "Евгений Заречнев",
    "profileName": "evikza",
    "avatar": "https://p16-sign-va.tiktokcdn.com/musically-maliva-obj/1666889735789574~c5_720x720.jpeg?x-expires=1624140000&x-signature=rQ9dhz8zGm9EnYNTtwG7uDX1kaw%3D",
    "description": "",
    "verified": false
  },
  "stats": {
    "following": 4,
    "follower": 0,
    "video": 0,
    "like": 0
  }
}
```

###### HTTP_CODE 404

```json
{
  "code": 404,
  "error": "This account cannot be found."
}
```

### Version

```code
0.0.1
```
