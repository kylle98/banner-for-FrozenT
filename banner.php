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

function checkIsImage($filepath)
{
    return pathinfo($filepath)['extension'] == "png" || pathinfo($filepath)['extension'] == "jpeg" || pathinfo($filepath)['extension'] == "jpg";
}

function convertJPGtoPNG($filepath)
{
    $imageExtension = pathinfo($filepath)['extension'];
    $imageBasename = basename($filepath, $imageExtension);
    $pathToImagesDirectory = __DIR__."/imgs/";
    imagepng(imagecreatefromstring(file_get_contents($pathToImagesDirectory.$filepath)), $pathToImagesDirectory.$imageBasename."png");
}

function removeJPG($filepath)
{
    $pathToImagesDirectory = __DIR__."/imgs/";
    unlink($pathToImagesDirectory.$filepath);
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
        if ( isNotDotOrDotDot($filepath) && checkIsImage($filepath) )
        {   
            $imageExtension = pathinfo($filepath)['extension'];
            
            if ($imageExtension == "jpg" || $imageExtension == "jpeg")
            {
                convertJPGtoPNG($filepath);
                removeJPG($filepath);
            }
            $imageWithoutExtension = getNameOfImage($filepath);
            
            if (file_exists(getJsonPath($imageWithoutExtension))) 
            {
                $imageArray[$indexOfImage++] = $filepath;
            }   
        }
    }
    
    $randomIndex = rand(0, count($imageArray) - 1);
    $chosenImage = $imageArray[$randomIndex];

    return $chosenImage;
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
    $image = imagecreatetruecolor($widhtImage,$heightImage); // Create main image 1200 x 480
    imagecopyresampled($image, $source, 0, 0, 0, 0, $widhtImage, $heightImage, $widhtImage, $heightImage);
    imagedestroy($source);

    return $image;
}

function displayImage($image)
{
    $filename = "finished.png";

    header('Content-Type: image/png');
    header("Content-Disposition: inline; filename=".$filename);
    imagepng($image);

}

function displaySavedImage()
{
    header('Content-Type: image/png');
    readfile( getBannerFilename() );
}

function saveImage($image)
{
    $filename = "finished.png";

    imagepng($image, $filename);
    imagedestroy($image);
}

function color($image)
{
    $colors = array(
        "white" => imagecolorallocate($image, 255,255,255),
        "blackshadow" => imagecolorallocate($image, 0,0,0),
        "color1" => imagecolorallocate($image, 203, 213, 231),
        "color2" => imagecolorallocate($image, 255,255,255),
        "gray " => imagecolorallocate($image, 200,200,200),
        "semi_purple_xd" => imagecolorallocate($image, 239, 229, 255)
    );

    return $colors;
}

function displayTextLine($image, $fontSize, $fontName, $label, $textColor, $yText, $shadowOffset)
{
    $colors = color($image);
    $borderOfImg = imagettfbbox($fontSize,0,getFontPath($fontName),$label);
    $leftCorner = $borderOfImg[0];
    $rightCorner = $borderOfImg[4];
    $squareCenterPosition = 820;
    $centerPosition = ($leftCorner + $rightCorner)/2;
    $yShadow = $yText + $shadowOffset;

    imagettftext($image, $fontSize, 0, $squareCenterPosition-$centerPosition, $yShadow, $colors['blackshadow'], getFontPath($fontName), $label); // Shadow
    imagettftext($image, $fontSize, 0, $squareCenterPosition-$centerPosition, $yText, $colors[$textColor], getFontPath($fontName), $label); // Text
}

function generateBanner() 
{
    $chosenImage = choosePhotoForBanner();
    $image = createImage($chosenImage);
    $basenameOfImage = getNameOfImage($chosenImage);

    $json_data = jsonDecode($basenameOfImage);
    $imageCountry = $json_data->{'Country'};
    $imageCity = $json_data->{'City'};
    $fontFilename = "SourceSansPro-Regular.otf";

    displayTextLine($image, 25, $fontFilename, "CEST timezone", 'color1', 84, 4);

    displayTextLine($image, 100, $fontFilename, date('H:i'), 'color2', 199, 4);

    displayTextLine($image, 35, $fontFilename, date('l'), 'semi_purple_xd', 253, 3);

    displayTextLine($image, 20, $fontFilename, $imageCity, 'color2', 334, 2);
    
    displayTextLine($image, 15, $fontFilename, $imageCountry, 'color2', 362, 2);

    displayTextLine($image, 35, $fontFilename, date('j F'), 'color2', 464, 4);

    displayImage($image);

    saveImage($image);
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
