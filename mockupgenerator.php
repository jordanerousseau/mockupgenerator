<?php
/**
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once __DIR__ . '/vendor/autoload.php';

use PrestaShop\Module\MockupGenerator\MGInstaller;

use PrestaShop\Module\MockupGenerator\MGMockup;
use PrestaShop\Module\MockupGenerator\MGProduct;

use PrestaShop\Module\MockupGenerator\MGProductOffsets;
use PrestaShop\Module\MockupGenerator\MGProductImage;
use PrestaShop\Module\MockupGenerator\MGDeleteProductImages;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MockupGenerator extends Module
{
    protected $config_form = false;

	public function __construct()
	{
		$this->name = 'mockupgenerator';
		$this->tab = 'front_office_features';
		$this->version = '1.0.5';
		$this->author = 'Wanted Design';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = [
			'min' => '1.7',
			'max' => _PS_VERSION_
		];

		$this->bootstrap = true;

		parent::__construct();

        $this->displayName = $this->l('Mockup Generator');
        $this->description = $this->l('Generates product images from mockups.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (!Configuration::get('MOCKUPGENERATOR_PAGENAME')) {
			$this->warning = $this->l('No name');
		}
	}
	
	public function install()
	{
		$installer = new MGInstaller();
		$installer->performInstallActions($this);
	
		return parent::install() &&
			$this->registerHook('actionProductUpdate') &&
			$this->registerHook('displayAdminProductsExtra');
	}

	public function uninstall()
	{
		$installer = new MGInstaller();
		$installer->performUninstallActions($this);

		return parent::uninstall();
	}

	public function hookActionProductUpdate($params)
	{

		$product = new Product($params['id_product']);

		// Check if there are mockups saved in the database
		$mockupData = MGMockup::getAll();
		
		if (!$mockupData) {
			return;
		}

		// Get submitted offsets
		$submittedValues = Tools::getAllValues();
		$submittedOffsets = [];

		foreach ($submittedValues as $key => $value) {
			if (strpos($key, 'mockup_offset_') === 0) {
				$mockupId = str_replace('mockup_offset_', '', $key);
				$submittedOffsets[$mockupId] = $value;
			}
		}

		// Save products offsets
		MGProductOffsets::saveProductMockupOffsets($product, $mockupData, $submittedOffsets);

		// Delete product images from mockups
		MGDeleteProductImages::deleteProductImages($product);

		// Generate product images from mockups
		MGProductImage::generateProductImages($product, $mockupData, $submittedOffsets);
	}

	public function hookDisplayAdminProductsExtra($params)
	{
		$mockupData = MGMockup::getAll();
		
		if (!$mockupData) {
			return;
		}
	
		$id_product = (int) $params['id_product'];
		$productMockupData = MGProduct::getById($id_product);
	
		$this->context->smarty->assign([
			'mockupData' => $mockupData,
			'productMockupData' => $productMockupData
		]);
	
		return $this->display(__FILE__, 'views/templates/admin/productextra.tpl');
	}
}
