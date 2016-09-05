<?php

require 'ImageStyle.php';

/**
 * Class LinkClickToPercentage
 */
class LinkClickToPercentage
{

  const LOWER_LEFT_XPOS = 0;
  const LOWER_RIGHT_XPOS = 2;
  const UPPER_LEFT_YPOS = 7;
  const LOWER_LEFT_YPOS = 1;
  const STYLES_CONFIG_FILE = 'styles/styles.json';
  const TRACKING_CONFIG_FILE = 'config/tracking.json';

  /**
   * @var array
   */
  private $result;

  /**
   * @var string
   */
  private $apiKey;

  /**
   * @var string
   */
  private $linkId;

  /**
   * @var string
   */
  private $emailName;

  /**
   * @var object
   */
  private $emailDetails;

  /**
   * @var array
   */
  private $stylesConfigs;

  /**
   * LinkClickToPercentage constructor.
   *
   * @param $apiKey
   * @param $emailId
   * @param $style
   */
  public function __construct($apiKey)
  {
    $this->apiKey = $apiKey; // api key linked to mailchimp account
    $this->linkId = isset($_GET['linkId']) ? $_GET['linkId'] : ''; // unique ident used to track each link
    $this->emailName = isset($_GET['emailName']) ? $_GET['emailName'] : ''; // name of email campaign in tracking.json
    $this->emailDetails = $this->getEmailDetails($this->emailName); // email config from tracking.json
    $this->stylesConfigs = json_decode(file_get_contents(self::STYLES_CONFIG_FILE), true); // generic style configs

    $this->requestData();
    $this->createImage();
  }

  /**
   * requestData
   * curl request to get data
   * from mailchimp
   * @return array
   */
  public function requestData()
  {
    $curl = curl_init();

    curl_setopt(
      $curl,
      CURLOPT_URL,
      $this->buildRequestUrl()
    );

    curl_setopt($curl, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Authorization: Basic ' . base64_encode('user:' . $this->apiKey),
    ]);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    return $this->result = json_decode(curl_exec($curl), true);
  }

  /**
   * getEmailDetails
   * retrieve email details from
   * tracking.json
   * @param  string $ident [email name]
   * @return object
   */
  private function getEmailDetails($ident)
  {
    $config = json_decode(file_get_contents(self::TRACKING_CONFIG_FILE), true);

    return $config[$ident];
  }

  /**
   * getStyleFromConfig
   * create the style from the style
   * specificed in the config json
   * @param  string $style
   * @return object
   */
  private function getStyleFromConfig()
  {
    return new ImageStyle(
      $this->stylesConfigs[$this->emailDetails['style']]['width'],
      $this->stylesConfigs[$this->emailDetails['style']]['height'],
      $this->stylesConfigs[$this->emailDetails['style']]['backgroundColor'],
      $this->stylesConfigs[$this->emailDetails['style']]['textSize'],
      $this->stylesConfigs[$this->emailDetails['style']]['textAngle'],
      $this->stylesConfigs[$this->emailDetails['style']]['fontFile'],
      $this->stylesConfigs[$this->emailDetails['style']]['textColor']
    );
  }

  /**
   * createImage
   * @return object
   */
  private function createImage()
  {
    $percentage = $this->getClickPercentage($this->result['urls_clicked'], $this->linkId); // get the percentage of links for url
    $style = $this->getStyleFromConfig();

    // create base image
    $img = imagecreatetruecolor($style->width, $style->height);
    imagefilledrectangle(
      $img,
      0,
      0,
      $style->width,
      $style->height,
      imagecolorallocate($img,
        $style->backgroundColor['r'],
        $style->backgroundColor['g'],
        $style->backgroundColor['b']
      )
    );

    // get text dimensions for centering
    $boundingBox = imagettfbbox(
      $style->textSize,
      $style->textAngle,
      $style->fontFile,
      $percentage
    );
    $textWidth = abs($boundingBox[self::LOWER_RIGHT_XPOS] - $boundingBox[self::LOWER_LEFT_XPOS]);
    $textHeight = abs($boundingBox[self::LOWER_LEFT_YPOS] - $boundingBox[self::UPPER_LEFT_YPOS]);
    $textX = round(($style->width / 2) - ($textWidth / 2));
    $textY = (round(($style->height / 2) + ($textHeight / 2))) - 2; // move it a couple pixels above centre

    // apply text to image
    imagettftext($img,
      $style->textSize,
      $style->textAngle,
      $textX,
      $textY,
      imagecolorallocate($img,
        $style->textColor['r'],
        $style->textColor['g'],
        $style->textColor['b']
      ),
      $style->fontFile,
      $percentage
    );

    $this->outputImage($img);
  }

  /**
   * outputImage
   * output the image as a png
   * @param $img
   */
  private function outputImage($img, $type = 'png')
  {
    header('Content-Type:image/' . $type);
    header("Pragma-directive: no-cache");
    header("Cache-directive: no-cache");
    header("Cache-control: no-cache");
    header("Pragma: no-cache");
    header("Expires: 0");

    imagepng($img);
  }

  /**
   * getClickPercentage
   * looks for a str match in the data
   * to get the percentage of times
   * the link has been clicked
   * @param  $array
   * @param  $str
   * @return string
   */
  private function getClickPercentage($array, $str)
  {
    foreach ($array as $key => $val) {
      if (strpos($val['url'], $str) !== false) {
        return (round($val['click_percentage'], 2) * 100) . '%';
      }
    }
    return null;
  }

  /**
   * @return string
   */
  private function buildRequestUrl()
  {
    return str_replace(
      '{{email_id}}',
      $this->emailDetails['emailId'],
      'https://us4.api.mailchimp.com/3.0/reports/{{email_id}}/click-details'
    );
  }
}
