<?php
date_default_timezone_set('CET');

$jsonDirectoryPath = __DIR__."/json/";

function isNotDotOrDotDot($filepath) 
{
    return $filepath != "." && $filepath != "..";
}

function getJsonPath($filename) 
{
    global $jsonDirectoryPath;
    return $jsonDirectoryPath.$filename.".json";
}

function getNameOfImage($image) 
{
    return basename($image, ".png");
}

function choosePhotoForBanner() 
{
    $indexOfImage = 0;
    $imageDirectoryPath = opendir('imgs');
    $imageArray = array();

    while ( $filepath = readdir($imageDirectoryPath) ) 
    {
        if ( isNotDotOrDotDot($filepath) )
        { 
            $imageWithoutExtension = getNameOfImage($filepath);
            $jsonSelect = getJsonPath($imageWithoutExtension);
            
            if (!file_exists($jsonSelect)) 
            {
                break;
            }
            $imageArray[$indexOfImage] = $filepath;
            $indexOfImage++;
        }
    }
    
    $randomIndex = rand(0, count($imageArray) - 1);
    $chosenImg = $imageArray[$randomIndex];

    return $chosenImg;
}

function jsonDecode($jsonFile) 
{
    $pathToJson = getJsonPath($jsonFile); 
    $jsonData = file_get_contents($pathToJson);

    return json_decode($jsonData);
}

function getFontPath()
{
    $nameFontFile = "SourceSansPro-Regular.otf";
    $fontPath = __DIR__.'/'.$nameFontFile;

    return $fontPath;
}

function getBannerFilename() 
{
    return __DIR__.'/finished.png';
}

function getModificationTimeOfFile($bannerFilename) 
{
    return filemtime($bannerFilename);
}

function createImage($chosenImg) 
{
    $widhtImage = 1200;
    $heightImage = 480;
    $source = imagecreatefrompng("imgs/".$chosenImg); // Chosen photo
    $createImg = imagecreatetruecolor(imagesx($source), imagesy($source)); // Create image with sizes of source image
    imagefill($createImg, 0, 0, imagecolorallocate($createImg, 255, 255, 255));
    imagealphablending($createImg, TRUE);
    imagecopy($createImg, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));
    imagedestroy($source);
    $source = $createImg;
    $img = imagecreatetruecolor($widhtImage,$heightImage); // Create main image 1200 x 480
    imagecopyresampled($img, $source, 0, 0, 0, 0, $widhtImage, $heightImage, $widhtImage, $heightImage);
    imagedestroy($source);

    return $img;
}

function displayAndSaveImg($img)
{
    $filename = "finished.png";

    header('Content-Type: image/png');
    header("Content-Disposition: inline; filename=".$filename);
    imagepng($img);
    imagepng($img, $filename);
    imagedestroy($img);
}

function color($img)
{
    $colors = array(
        "white" => imagecolorallocate($img, 255,255,255),
        "blackshadow" => imagecolorallocate($img, 0,0,0),
        "color1" => imagecolorallocate($img, 203, 213, 231),
        "color2" => imagecolorallocate($img, 255,255,255),
        "gray " => imagecolorallocate($img, 200,200,200),
        "semi_purple_xd" => imagecolorallocate($img, 239, 229, 255)
    );

    return $colors;
}

function doTimezoneOnImg($img)
{
    $colors = color($img);
    $fontSize = 25;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath(),"CEST timezone");
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 84;
    $heightWithText = 80;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath(), "CEST timezone"); // Shadow
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['color1'], getFontPath(), "CEST timezone"); // Text
}

function doTimeOnImg($img)
{
    $colors = color($img);
    $fontSize = 100;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath(),date('H:i'));
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 199;
    $heightWithText = 195;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath(), date('H:i')); // Shadow
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['color2'], getFontPath(), date('H:i')); // Text
}

function doNameOfDayOnImg($img)
{
    $colors = color($img);
    $fontSize = 35;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath(),date('l'));
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 253;
    $heightWithText = 250;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath(), date('l')); // Shadow
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['semi_purple_xd'], getFontPath(), date('l')); // Text
}

function doCityNameOnImg($img, $imageCity)
{
    $colors = color($img);
    $fontSize = 20;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath(),$imageCity);
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 334;
    $heightWithText = 331;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath(), $imageCity); // Text
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['color2'], getFontPath(), $imageCity); // Shadow
}

function doCountryNameOnImg($img, $imageCountry)
{
    $colors = color($img);
    $fontSize = 15;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath(),$imageCountry);
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 362;
    $heightWithText = 360;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath(), $imageCountry); // Text
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['color2'], getFontPath(), $imageCountry); // Shadow
}

function doDateOnImg($img)
{
    $colors = color($img);
    $fontSize = 35;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath(),date('j F'));
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 464;
    $heightWithText = 460;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath(), date('j F')); // Shadow
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['color2'], getFontPath(), date('j F')); // Text
}

function generateBanner() 
{
    $img = createImage( choosePhotoForBanner() );

    $json_data = jsonDecode( getNameOfImage( choosePhotoForBanner() ) );
    $imageCountry = $json_data->{'Country'};
    $imageCity = $json_data->{'City'};

    doTimezoneOnImg($img);

    doTimeOnImg($img);

    doNameOfDayOnImg($img);

    doCityNameOnImg($img, $imageCity);
    
    doCountryNameOnImg($img, $imageCountry);

    doDateOnImg($img);

    displayAndSaveImg($img);
}

function main() {
    $neededTimeToGenerateBanner = 30;
    if (file_exists( getBannerFilename() ) ) 
    {
        if ( ( time() - getModificationTimeOfFile( getBannerFilename() ) ) > $neededTimeToGenerateBanner ) 
        {
            generateBanner();
        }
        else 
        {
            header('Content-Type: image/png');
            readfile( getBannerFilename() );
        }
    }
    else 
    {
        generateBanner();
    }
}
main();
?>
