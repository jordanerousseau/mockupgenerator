<?php

namespace PrestaShop\Module\MockupGenerator;

use PrestaShop\PrestaShop\Adapter\Entity\ObjectModel;

class MGMockup extends ObjectModel
{
    public $id_mockup;
    public $name;
    public $image;
    public $offset_x;
    public $offset_y;

    public static $definition = [
        'table' => 'mockup',
        'primary' => 'id_mockup',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'offset_x' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
            'offset_y' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
        ],
    ];

    public static function getAll()
    {
        return \Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'mockup`');
    }

    public static function getByName($name)
    {
        $query = new \DbQuery();
        $query->select('id_mockup');
        $query->from('mockup');
        $query->where('name = \'' . pSQL($name) . '\'');

        $result = \Db::getInstance()->getValue($query);

        if (!$result) {
            return false;
        }

        return new self($result);
    }
}
