<?php

use PrestaShop\Module\MockupGenerator\MGMockup;
use PrestaShop\Module\MockupGenerator\MGMockupUploader;

class AdminMockupsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mockup';
        $this->className = 'PrestaShop\Module\MockupGenerator\MGMockup';
        $this->lang = false;
        $this->explicitSelect = true;
        $this->allow_export = false;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->multishop_context = Shop::CONTEXT_ALL;

        parent::__construct();

		$this->fields_list = [
            'id_mockup' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25,
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title' => $this->l('Name'),
                'width' => 'auto',
            ],
            'image' => [
                'title' => $this->l('Image'),
                'width' => 'auto',
                'image' => 'm/mockups',
            ],
        ];
    }

    public function renderForm()
    {
        // Check if existing mockup
        $isExistingMockup = false;
        if ($this->object->id) {
            $isExistingMockup = true;
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Mockup'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'required' => true,
                    'readonly' => $isExistingMockup ? true : false,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Offset X'),
                    'name' => 'offset_x',
                    'required' => true,
                    'default_value' => '0',
                    'desc' => $this->l('Enter the X offset for the mockup image.'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Offset Y'),
                    'name' => 'offset_y',
                    'required' => true,
                    'default_value' => '0',
                    'desc' => $this->l('Enter the Y offset for the mockup image.'),
                ],
                [
                    'type' => 'file',
                    'label' => $this->l('Image'),
                    'name' => 'image',
                    'required' => $isExistingMockup ? false : true,
                    'desc' => $isExistingMockup ? $this->l('Upload a new image to replace the existing one or leave empty to keep the existing image.') : '',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
            ],
        ];

        return parent::renderForm();
    }
	
    public function postProcess()
    {
        $result = false;
    
        if (Tools::isSubmit('submitAddmockup')) {
            $file = $_FILES['image'];
            $mockupName = Tools::getValue('name');
    
            // Check if mockup already exists
            $idMockup = (int) Tools::getValue('id_mockup');
            $mockup = new MGMockup($idMockup);
            $isExistingMockup = Validate::isLoadedObject($mockup);
            $imageRequired = !$isExistingMockup;
    
            // Validate form fields
            $errors = [];
            if (empty($mockupName)) {
                $errors[] = $this->l('Name is required.');
            }
            if ($imageRequired && empty($file['name'])) {
                $errors[] = $this->l('Image is required.');
            }
            if ($isExistingMockup && $mockup->name != $mockupName) {
                $errors[] = $this->l('Cannot change mockup name.');
            }
            if ($mockup->name != $mockupName && MGMockup::getByName($mockupName) instanceof MGMockup) {
                $errors[] = $this->l('A mockup with this name already exists.');
            }
    
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->errors[] = $error;
                }
                $this->display = 'edit';
            } else {
                // Save mockup data
                $result = parent::postProcess();
    
                // Upload or update mockup image
                if ($imageRequired && $file['error'] == 0) {
                    $mockupId = $this->object->id;
                    $imageName = MGMockupUploader::uploadMockupImage($file, $mockupId, $mockupName);
    
                    if ($imageName !== false) {
                        $mockup = new MGMockup($mockupId);
                        $mockup->image = $imageName;
                        $mockup->update();
                    } else {
                        $this->errors[] = $this->l('Image upload failed.');
                        $this->display = 'edit';
                    }
                } elseif (!$imageRequired && !empty($file['name']) && $file['error'] == 0) {
                    // Update mockup image
                    $imageName = MGMockupUploader::uploadMockupImage($file, $idMockup, $mockupName);
    
                    if ($imageName !== false) {
                        $mockup->image = $imageName;
                        $mockup->update();
                    } else {
                        $this->errors[] = $this->l('Image upload failed.');
                        $this->display = 'edit';
                    }
                } elseif (!$imageRequired && empty($file['name']) && $isExistingMockup) {
                    // Update mockup data only
                    if (Tools::getValue('name') != '') {
                        $mockup->name = Tools::getValue('name');
                    }
                    if (Tools::getValue('offset_x') != '') {
                        $mockup->offset_x = (int) Tools::getValue('offset_x');
                    }
                    if (Tools::getValue('offset_y') != '') {
                        $mockup->offset_y = (int) Tools::getValue('offset_y');
                    }
                    $mockup->update();
                }
            }
        } else {
            $result = parent::postProcess();
        }
    
        return $result;
    }
	
    public function renderList()
    {
        $this->addRowAction('delete');
    
        return parent::renderList();
    }
  
    public function processDelete()
    {
        if (!$this->tabAccess['delete']) {
            die(Tools::jsonEncode(['error' => $this->l('Access denied')]));
        }
    
        $idMockup = intval(Tools::getValue('id_mockup'));
        $mockup = new MGMockup($idMockup);
   
        if (Validate::isLoadedObject($mockup)) {
            $imageDir = _PS_IMG_DIR_ . 'm/mockups/';
    
            if (file_exists($imageDir . $mockup->id . '.jpg')) {
                unlink($imageDir . $mockup->id . '.jpg');
            }
    
            if (file_exists($imageDir . $mockup->id . '.png')) {
                unlink($imageDir . $mockup->image . '.png');
            }

            if (file_exists($imageDir . $mockup->image)) {
                unlink($imageDir . $mockup->image);
            }

            // Delete mockup thumbnail cache files
            $cacheDir = _PS_IMG_DIR_ . 'tmp/';
            $files = scandir($cacheDir);
            foreach ($files as $file) {
                if (strpos($file, 'mockup_mini_'.$mockup->id.'_') === 0) {
                    $filePath = $cacheDir . $file;
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
    
            $mockup->delete();
    
            $this->confirmations[] = $this->l('Mockup successfully deleted.');
        } else {
            $this->errors[] = $this->l('Invalid mockup.');
        }
    }
}
