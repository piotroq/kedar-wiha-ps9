<?php
//require_once(dirname(__FILE__).'../../../../../../config/config.inc.php');

class CaptchaSecurityImages
{

    private $font = '../../views/fonts/monofont.ttf';
    
    public function __construct($width = '120', $height = '40', $characters = '6')
    {
        $fontPath = getcwd().'/'.$this->font;
        $code = $this->generateCode($characters);
        /* font size will be 75% of the image height */
        $font_size = $height * 0.75;
        $image = @imagecreate($width, $height) or die('Cannot initialize new GD image stream');
        /* set the colours */
        imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 20, 40, 100);
        $noise_color = imagecolorallocate($image, 100, 120, 180);
        /* generate random dots in background */
        for ($i = 0; $i < ($width * $height) / 3; $i++) {
            imagefilledellipse($image, mt_rand(0, $width), mt_rand(0, $height), 1, 1, $noise_color);
        }
        /* generate random lines in background */
        for ($i = 0; $i < ($width * $height) / 150; $i++) {
            imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $noise_color);
        }
				$fontPath =  _PS_MODULE_DIR_."smartblog/classes/../views/fonts/monofont.ttf";
        /* create textbox and add text */
        $textbox = imagettfbbox($font_size, 0, $fontPath, $code) or die('Error in imagettfbbox function');
        $x = ($width - $textbox[4]) / 2;
        $y = ($height - $textbox[5]) / 2;
        imagettftext($image, $font_size, 0, $x, $y, $text_color, $fontPath, $code) or die('Error in imagettftext function');
        /* output captcha image to browser */
        $context = Context::getContext();
//        $context->cookie->ssmartblogcaptcha = $code;
        $context->cookie->__set('ssmartblogcaptcha',$code);
        $context->cookie->write();
        
        header('Content-Type: image/jpeg');
        imagejpeg($image);
        imagedestroy($image);
       
        //$_SESSION['ssmartblogcaptcha'] = $code;
    }
    public function generateCode($characters)
    {
        /* list all possible characters, similar looking characters and vowels have been removed */
        $possible = '23456789bcdfghjkmnpqrstvwxyz';
        $code = '';
        $i = 0;
        while ($i < $characters) {
            $code .= Tools::substr($possible, mt_rand(0, Tools::strlen($possible) - 1), 1);
            $i++;
        }
        return $code;
    }

    

}

class SmartBlogCapChaModuleFrontController extends ModuleFrontController
{
    private $variables = [];
	
    public function init()
    {
        parent::init();
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
     $width = Tools::getIsset('width') ? Tools::getValue('width') : '120';
		$height = Tools::getIsset('height') ? Tools::getValue('height') : '40';
		$characters = Tools::getIsset('characters') && Tools::getValue('characters') > 1 ? Tools::getValue('characters') : '6';

		$captcha = new CaptchaSecurityImages($width, $height, $characters);
		
			die();
    }

}
