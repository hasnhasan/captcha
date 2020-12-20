# captcha
Captcha Decoder

$imgUrl = '';
$capcha = Captcha::via('bypass');
$response  =$capcha->decode($imgUrl);
