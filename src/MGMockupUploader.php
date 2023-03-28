<?php

namespace PrestaShop\Module\MockupGenerator;

use PrestaShop\PrestaShop\Adapter\Tools;

class MGMockupUploader
{
    public static function uploadMockupImage($file, $id, $name)
    {
        if (!empty($file['name'])) {
            $tools = new Tools();

            $imageDir = _PS_IMG_DIR_ . 'm/mockups/';
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $imageName = $tools->link_rewrite($name) . '.' . $ext;
            $thumbnailName = $id . '.jpg';
    
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $tools->displayError($this->l('Invalid image format.'));
                return false;
            }
    
            if (!is_dir($imageDir)) {
                mkdir($imageDir, 0755, true);
            }

            // Remove cached images
            $cacheDir = _PS_TMP_IMG_DIR_;
            $cacheFiles = scandir($cacheDir);

            foreach ($cacheFiles as $cacheFile) {
                if (strpos($cacheFile, 'mockup_mini_'.$id.'_') === 0) {
                    unlink($cacheDir . $cacheFile);
                }
            }
   
            // Save the original image
            if (move_uploaded_file($file['tmp_name'], $imageDir . $imageName)) {
                // If the original image is not JPG, create a JPG thumbnail
                if ($ext != 'jpg' && $ext != 'jpeg') {
                    $image = @imagecreatefromstring(file_get_contents($imageDir . $imageName));

                    // Create a true color image for better quality

                    // Create a true color image for better quality
                    $thumbnail = imagecreatetruecolor(imagesx($image), imagesy($image));

                    // If the original image is PNG, preserve transparency
                    if ($ext == 'png') {
                        imagesavealpha($thumbnail, true);
                        $transparentColor = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
                        imagefill($thumbnail, 0, 0, $transparentColor);
                    }

                    imagecopy($thumbnail, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

                    // Save the JPG thumbnail
                    imagejpeg($thumbnail, $imageDir . $thumbnailName, 100);

                    // Free up memory
                    imagedestroy($thumbnail);
                }
                return $imageName;
            }
        }

        return false;
    }
}
