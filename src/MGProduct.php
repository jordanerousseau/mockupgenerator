<?php

namespace PrestaShop\Module\MockupGenerator;

use PrestaShop\PrestaShop\Adapter\Entity\ObjectModel;

class MGProduct extends ObjectModel
{
    public $id_product_mockup;
    public $id_product;
    public $id_mockup;
    public $offset_x;
    public $offset_y;

    public static $definition = [
        'table' => 'product_mockup',
        'primary' => 'id_product_mockup',
        'fields' => [
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_mockup' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'offset_x' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
            'offset_y' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
        ],
    ];

    public static function getAll()
    {
        return \Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'product_mockup`');
    }

    public static function getById($id)
    {
        return \Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'product_mockup` WHERE `id_product` = ' . (int) $id);
    }

    public static function getByProductIdAndMockupId($id_product, $id_mockup)
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('product_mockup');
        $sql->where('id_product = ' . (int) $id_product . ' AND id_mockup = ' . (int) $id_mockup);

        $result = \Db::getInstance()->getRow($sql);

        if ($result) {
            $mgProduct = new MGProduct();
            $mgProduct->hydrate($result);
            return $mgProduct;
        }

        return null;
    }
}
