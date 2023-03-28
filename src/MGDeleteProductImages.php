<?php

namespace PrestaShop\Module\MockupGenerator;

use Product;
use Context;

class MGDeleteProductImages
{
    public static function deleteProductImages(Product $product)
    {
        // Get all images associated with the product
        $images = \Image::getImages(Context::getContext()->language->id, $product->id);
    
        foreach ($images as $image) {
            if (strpos($image['legend'], 'Mockup') !== false) {
                // Create an Image object
                $imageObj = new \Image($image['id_image']);
    
                // Delete the image from the server
                $types = array('large_default', 'home_default', 'category_default', 'medium_default', 'small_default', 'cart_default');
                foreach ($types as $type) {
                    $thumbPath = _PS_PROD_IMG_DIR_ . $imageObj->getImgPath($type) . '.jpg';
    
                    if (file_exists($thumbPath)) {
                        unlink($thumbPath);
                    }
                }
    
                $path = _PS_PROD_IMG_DIR_ . $imageObj->getImgPath() . '.jpg';
                if (file_exists($path)) {
                    unlink($path);
                }
    
                // Delete the image from the database
                $imageObj->delete();
            }
        }
    }
}
