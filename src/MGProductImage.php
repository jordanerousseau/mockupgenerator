<?php

namespace PrestaShop\Module\MockupGenerator;

use Intervention\Image\ImageManager;

use Product;
use Language;

class MGProductImage
{
	public static function generateProductImages(Product $product, $mockupData, $submittedOffsets)
	{
		// Make sure the Intervention Image library is installed
		if (!class_exists('Intervention\Image\ImageManagerStatic')) {
			return;
		}

        // Get the product cover image
        $image = $product->getCover($product->id);

        // Create an instance of the ImageManager
        $imageManager = new ImageManager();

        // Get the product image path
        $imageId = $product->getCoverWs();
        $productImageObject = new \Image($imageId);
        $productImagePath = _PS_IMG_DIR_ . 'p/' . $productImageObject->getExistingImgPath() . '.jpg';

		// Process each mockup
		foreach ($mockupData as $mockupItem) {

			// Load data specific mockup
			$mockupPath = _PS_IMG_DIR_ . 'm/mockups/' . $mockupItem['image'];
			
			$offsetX = isset($submittedOffsets['x_'.$mockupItem['id_mockup']]) ? (int) $submittedOffsets['x_'.$mockupItem['id_mockup']] : (int) $mockupItem['offset_x'];
			$offsetY = isset($submittedOffsets['y_'.$mockupItem['id_mockup']]) ? (int) $submittedOffsets['y_'.$mockupItem['id_mockup']] : (int) $mockupItem['offset_y'];

			// Load mockup image
			if (!file_exists($mockupPath)) {
				continue;
			}

			$mockupImage = $imageManager->make($mockupPath);

			// Load product image
			$productImage = $imageManager->make($productImagePath);

			// Get the background color of the product image
			$backgroundColor = $productImage->pickColor(0, 0, 'hex');
			
			// Calculate optimal width and height based on mockup dimensions
			$mockupWidth = $mockupImage->getWidth();
			$mockupHeight = $mockupImage->getHeight();
			$heightPercentage = 0.3; // Adjust value to fit the product image within the mockup
			$optimalHeight = $mockupHeight * $heightPercentage;
			
			// Resize product image maintaining aspect ratio
			$productImage->resize(null, $optimalHeight, function ($constraint) {
				$constraint->aspectRatio();
			});

			// Calculate the product image position to center it on the mockup
			$productImageWidth = $productImage->getWidth();
			$productImageHeight = $productImage->getHeight();
			$positionX = ($mockupWidth - $productImageWidth) / 2 + $offsetX;
			$positionY = ($mockupHeight - $productImageHeight) / 2 + $offsetY;

			// Create a new canvas with the product image background color
			$canvas = $imageManager->canvas($mockupWidth, $mockupHeight, $backgroundColor);

			// Insert the product image on the canvas
			$canvas->insert($productImage, 'top-left', (int) $positionX, (int) $positionY);

			// Apply mockup to product image
			$canvas->insert($mockupImage, 'center');

			// Apply mockup to product image
			$canvas->insert($mockupImage, 'center');

			// Generated image product
			$newImage = new \Image();
			$newImage->id_product = $product->id;
			$newImage->position = \Image::getHighestPosition($product->id) + 1;
			$newImage->cover = false;
			if (($languages = Language::getLanguages(true)) === false) {
				return false;
			}
			foreach ($languages as $language) {
				$newImage->legend[$language['id_lang']] = 'Mockup';
			}
			if (!$newImage->add()) {
				return false;
			}

			$newImage->associateTo($product->id);
			
			// Save the generated image
			$newImagePath = _PS_PROD_IMG_DIR_ . $newImage->getImgPath() . '.jpg';
			
			// Create missing directories if necessary
			$newImageDir = dirname($newImagePath);
			if (!file_exists($newImageDir)) {
				mkdir($newImageDir, 0755, true);
			}

			if (!file_exists($newImagePath)) {
				$canvas->save($newImagePath, 90); // Save as JPG with 90% quality
			}
			
			// Generate thumbnails
			$types = array('large_default', 'home_default', 'category_default', 'medium_default', 'small_default', 'cart_default');
			foreach ($types as $type) {
				// Get the thumbnail size
				$thumbnailSize = \Image::getSize($type);

				// Create the thumbnail
				$thumbnail = $canvas->fit($thumbnailSize['width'], $thumbnailSize['height']);

				// Save the thumbnail
				$thumbPath = _PS_PROD_IMG_DIR_ . $newImage->getImgPath($type) . '.jpg';

				// Create missing directories if necessary
				$thumbDir = dirname($thumbPath);
				if (!file_exists($thumbDir)) {
					mkdir($thumbDir, 0755, true);
				}

				if (!file_exists($thumbPath)) {
					$thumbnail->save($thumbPath, 90); // Save as JPG with 90% quality
				}
			}
		}
	}
}
