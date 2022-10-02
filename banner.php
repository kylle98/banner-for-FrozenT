<?php
date_default_timezone_set('CET');

function isNotDotOrDotDot($filepath) 
{
    return $filepath != "." && $filepath != "..";
}

function getJsonPath($filename) 
{
    $jsonDirectoryPath = __DIR__."/json/";
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
            $imageArray[$indexOfImage++] = $filepath;
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

function getFontPath($fontName)
{
    $fontPath = __DIR__.'/'.$fontName;

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

function displayImage($img)
{
    $filename = "finished.png";

    header('Content-Type: image/png');
    header("Content-Disposition: inline; filename=".$filename);
    imagepng($img);

}

function displaySavedImage()
{
    header('Content-Type: image/png');
    readfile( getBannerFilename() );
}

function saveImage($img)
{
    $filename = "finished.png";

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

function displayTextLine($fontSize, $fontName, $label, $textColor, $yShadow, $yText)
{
    $colors = color($img);
    $fontSize = 25;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath("SourceSansPro-Regular.otf"),"CEST timezone");
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 84;
    $heightWithText = 80;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath("SourceSansPro-Regular.otf"), "CEST timezone"); // Shadow
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['color1'], getFontPath("SourceSansPro-Regular.otf"), "CEST timezone"); // Text
}

function doTimezoneOnImg($img)
{
    $colors = color($img);
    $fontSize = 25;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath("SourceSansPro-Regular.otf"),"CEST timezone");
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 84;
    $heightWithText = 80;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath("SourceSansPro-Regular.otf"), "CEST timezone"); // Shadow
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['color1'], getFontPath("SourceSansPro-Regular.otf"), "CEST timezone"); // Text
}

function doTimeOnImg($img)
{
    $colors = color($img);
    $fontSize = 100;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath("SourceSansPro-Regular.otf"),date('H:i'));
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 199;
    $heightWithText = 195;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath("SourceSansPro-Regular.otf"), date('H:i')); // Shadow
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['color2'], getFontPath("SourceSansPro-Regular.otf"), date('H:i')); // Text
}

function doNameOfDayOnImg($img)
{
    $colors = color($img);
    $fontSize = 35;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath("SourceSansPro-Regular.otf"),date('l'));
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 253;
    $heightWithText = 250;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath("SourceSansPro-Regular.otf"), date('l')); // Shadow
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['semi_purple_xd'], getFontPath("SourceSansPro-Regular.otf"), date('l')); // Text
}

function doCityNameOnImg($img, $imageCity)
{
    $colors = color($img);
    $fontSize = 20;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath("SourceSansPro-Regular.otf"),$imageCity);
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 334;
    $heightWithText = 331;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath("SourceSansPro-Regular.otf"), $imageCity); // Text
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['color2'], getFontPath("SourceSansPro-Regular.otf"), $imageCity); // Shadow
}

function doCountryNameOnImg($img, $imageCountry)
{
    $colors = color($img);
    $fontSize = 15;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath("SourceSansPro-Regular.otf"),$imageCountry);
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 362;
    $heightWithText = 360;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath("SourceSansPro-Regular.otf"), $imageCountry); // Text
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['color2'], getFontPath("SourceSansPro-Regular.otf"), $imageCountry); // Shadow
}

function doDateOnImg($img)
{
    $colors = color($img);
    $fontSize = 35;
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath("SourceSansPro-Regular.otf"),date('j F'));
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $heightWithShadow = 464;
    $heightWithText = 460;
    $centerPosition = ($leftCorner + $rightCorner)/2;

    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithShadow, $colors['blackshadow'], getFontPath("SourceSansPro-Regular.otf"), date('j F')); // Shadow
    imagettftext($img, $fontSize, 0, 820-$centerPosition, $heightWithText, $colors['color2'], getFontPath("SourceSansPro-Regular.otf"), date('j F')); // Text
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

    displayImage($img);

    saveImage($img);
}

function main() {
    $neededTimeToGenerateBanner = 30;
    if ( file_exists(getBannerFilename()) ) 
    {
        $bannerFile = getBannerFilename();
        $timeOfModificationBanner = getModificationTimeOfFile($bannerFile);
        $timeDifference = time() - $timeOfModificationBanner;

        if ($timeDifference > $neededTimeToGenerateBanner) 
        {
            generateBanner();
        }
        else 
        {
            displaySavedImage();
        }
    }
    else 
    {
        generateBanner();
    }
}
main();
?>
