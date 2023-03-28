Mockup Generator
================

Description
-----------
This module generates product images from multiple mockups when adding or updating a product in PrestaShop.

Features
--------
- Generates product images from multiple mockups
- Saves generated images and associates them with the product
- Deletes mockup images when uninstalling the module
- Supports English and French translations

Installation
------------
1. Upload the `mockupgenerator` folder to the `modules` directory in your PrestaShop installation.
2. Go to the "Modules" section in your PrestaShop admin panel.
3. Find the "Mockup Generator" module in the module list and click "Install".
4. Make sure you have the Intervention Image library installed by running `composer require intervention/image` in the `mockupgenerator` directory.

Usage
-----
After installing the module, it will automatically generate product images from mockups when you add or update a product. Configure the mockup and image positions in the Catalog > Mockups tab.
