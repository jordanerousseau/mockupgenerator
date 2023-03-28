<?php

namespace PrestaShop\Module\MockupGenerator;

use PrestaShop\Module\MockupGenerator\MGDeleteProductImages;

use Product;
use Context;
use Tab;
use Db;
use Language;

class MGInstaller
{
	public function performInstallActions($module)
	{
		// Install database
		$this->installDb();
		
		// Install Tab
		$this->installTab($module);
	}

	public function performUninstallActions($module)
    {
        // Get all products
        $products = Product::getProducts(Context::getContext()->language->id, 0, 0, 'id_product', 'ASC');

        // Instantiate the MGDeleteProductImages class
        $imageDeleter = new MGDeleteProductImages();

        // Iterate through each product and delete associated mockup images
        foreach ($products as $productData) {
            $product = new Product($productData['id_product']);
            $imageDeleter->deleteProductImages($product);
        }

        // Remove mockups
        $mockupDir = _PS_IMG_DIR_ . 'm/mockups/';
        if (file_exists($mockupDir)) {
            $mockupFiles = glob($mockupDir . '*');
            foreach ($mockupFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            rmdir($mockupDir);
        }

        // Remove the actionProductUpdate hook
        if (!$module->unregisterHook('actionProductUpdate')) {
            return false;
        }

        // Remove the displayAdminProductsExtra hook
        if (!$module->unregisterHook('displayAdminProductsExtra')) {
            return false;
        }

        // Uninstall database
        if (!$this->uninstallDb()) {
            return false;
        }

        // Uninstall Tab
        if (!$this->uninstallTab()) {
            return false;
        }
    }

	private function installDb()
	{
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mockup` (
			`id_mockup` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL,
			`image` varchar(255) NOT NULL,
			`offset_x` int(10) NOT NULL DEFAULT 0,
			`offset_y` int(10) NOT NULL DEFAULT 0,
			PRIMARY KEY (`id_mockup`)
		) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

		if (!Db::getInstance()->execute($sql)) {
			return false;
		}

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'product_mockup` (
            `id_product_mockup` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_mockup` INT(10) UNSIGNED NOT NULL,
            `offset_x` INT(10) NOT NULL DEFAULT 0,
            `offset_y` INT(10) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_product_mockup`),
            UNIQUE KEY `product_mockup_offset` (`id_product`, `id_mockup`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
	
		return Db::getInstance()->execute($sql);
	}

	private function installTab($module)
	{
		$tab = new Tab();
		$tab->active = 1;
		$tab->class_name = 'AdminMockups';
		$tab->name = [];

		foreach (Language::getLanguages(true) as $lang) {
			$tab->name[$lang['id_lang']] = 'Mockups';
		}

		$tab->id_parent = (int) Tab::getIdFromClassName('AdminCatalog');
        $tab->module = $module->name;
        return $tab->add();
	}

	private function uninstallDb()
	{
		$sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mockup`';

		if (!Db::getInstance()->execute($sql)) {
			return false;
		}

		$sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'product_mockup`';

		return Db::getInstance()->execute($sql);
	}	

	private function uninstallTab()
	{
		$id_tab = (int) Tab::getIdFromClassName('AdminMockups');
		if ($id_tab) {
			$tab = new Tab($id_tab);
			return $tab->delete();
		}
	
		return false;
	}
}
