<?php

namespace PrestaShop\Module\MockupGenerator;

use PrestaShop\PrestaShop\Adapter\Entity\ObjectModel;

class MGProductOffsets extends MGProduct
{
    public static function saveProductMockupOffsets($product, $mockupData, $submittedOffsets)
    {
        foreach ($mockupData as $mockup) {
            $offsetXKey = 'x_' . $mockup['id_mockup'];
            $offsetYKey = 'y_' . $mockup['id_mockup'];
    
            // Check if custom offsets were submitted for this mockup
            if (isset($submittedOffsets[$offsetXKey]) && isset($submittedOffsets[$offsetYKey])) {
                $offsetX = (float) $submittedOffsets[$offsetXKey];
                $offsetY = (float) $submittedOffsets[$offsetYKey];
    
                // Check if a product_mockup entry already exists
                $productMockup = MGProduct::getByProductIdAndMockupId($product->id, $mockup['id_mockup']);
    
                if ($productMockup) {
                    // Update existing product_mockup entry
                    $productMockup->offset_x = $offsetX;
                    $productMockup->offset_y = $offsetY;
                    $productMockup->update();
                } else {
                    // Create new product_mockup entry
                    $newProductMockup = new MGProduct();
                    $newProductMockup->id_product = (int) $product->id;
                    $newProductMockup->id_mockup = (int) $mockup['id_mockup'];
                    $newProductMockup->offset_x = $offsetX;
                    $newProductMockup->offset_y = $offsetY;
                    $newProductMockup->add();
                }
            }
        }
    }
}
