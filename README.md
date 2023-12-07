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
    "avatar": "https://p16-sign-sg.tiktokcdn.com/aweme/720x720/tos-alisg-avt-0068/fe013a3c3fdb0e10e12edd1473d248f5.jpeg?x-expires=1674608400&x-signature=8CAqRvVxqiIiWXyjk1u4kKcFslQ%3D",
    "description": "",
    "region": "RU",
    "verified": false
  },
  "stats": {
    "following": 10,
    "follower": 1,
    "video": 2,
    "like": 3
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
0.0.1 — Fixed User-Agent.

0.0.2 — Fixed User-Agent.
        Fixed (regex) lookup of JSON data on the page.

0.0.3 — Fixed (regex) lookup of JSON data on the page.
```
