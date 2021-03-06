<?php

use Winged\Model\Model;
use Winged\Formater\Formater;
use Winged\Validator\Validator;
use Winged\Utils\RandomName;
use Winged\Winged;

/**
 * Class SeoPages
 */
class SeoPages extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    public $folder = './uploads/ogimage/';

    /** @var $id_seo integer */
    public $id_seo;

    /** @var $page_title string */
    public $page_title;

    /** @var $description string */
    public $description;

    /** @var $fb_title string */
    public $fb_title;

    /** @var $fb_description string */
    public $fb_description;

    /** @var $canonical_url string */
    public $canonical_url;

    /** @var $fb_image string */
    public $fb_image;

    /** @var $slug string */
    public $slug;

    /** @var $keywords string */
    public $keywords;

    /**
     * @return string
     */
    public static function tableName()
    {
        return "seo_pages";
    }

    /**
     * @return string
     */
    public static function primaryKeyName()
    {
        return "id_seo";
    }

    /**
     * @param bool $pk
     *
     * @return $this|int|Model
     */
    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_seo = $pk;
            return $this;
        }
        return $this->id_seo;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'page_title' => function () {
                if ($this->slug == '') {
                    $this->slug = $this->page_title;
                }
            },
            'slug' => function () {
                return Formater::toUrl($this->slug);
            },
            'fb_image' => function () {
                return (new UploadAbstract())->process_posted_image($this, 'fb_image', Formater::toUrl($this->page_title));
            },
        ];
    }

    /**
     * @return array
     */
    public function reverseBehaviors()
    {
        return [];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'page_title' => [
                'required' => true,
                'length' => function () {
                    return Validator::lengthSmallerOrEqual($this->page_title, 154);
                }
            ],
            'description' => [
                'required' => true,
                'length' => function () {
                    return Validator::lengthSmallerOrEqual($this->description, 157);
                }
            ],
            'fb_title' => [
                'required' => true,
                'length' => function () {
                    return Validator::lengthSmallerOrEqual($this->fb_title, 260);
                }
            ],
            'fb_description' => [
                'required' => true,
                'length' => function () {
                    return Validator::lengthSmallerOrEqual($this->fb_description, 504);
                }
            ],
            'fb_image' => [
                'required' => true,
            ],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'page_title' => [
                'required' => 'Este campo é obrigatório.',
                'length' => 'O limite de caracteres de 150 foi excedido.'
            ],
            'description' => [
                'required' => 'Este campo é obrigatório.',
                'length' => 'O limite de caracteres de 153 foi excedido.'
            ],
            'fb_title' => [
                'required' => 'Este campo é obrigatório.',
                'length' => 'O limite de caracteres de 255 foi excedido.'
            ],
            'fb_description' => [
                'required' => 'Este campo é obrigatório.',
                'length' => 'O limite de caracteres de 500 foi excedido.'
            ],
            'fb_image' => [
                'required' => 'O envio de uma imagem para o Facebook é extremamente necessaria.'
            ],
        ];
    }

    /**
     * @return array
     */
    public function labels()
    {
        return [
            'page_title' => 'Título H1 da página: ',
            'description' => 'Meta description: ',
            'fb_title' => 'Título do Facebook: ',
            'fb_description' => 'Descrição do Facebook: ',
            'canonical_url' => 'URL Canonica: ',
            'fb_image' => 'Imagem do Facebook: ',
            'slug' => 'URL da página: ',
            'keywords' => 'Palavra chave: '
        ];
    }

    /**
     * @param string $field
     *
     * @return bool|string
     */
    public function getImagem($field = 'fb_image')
    {
        if (property_exists(get_class($this), $field)) {
            if ($this->{$field} != '') {
                if (file_exists($this->folder . $this->{$field})) {
                    if (WingedConfig::$USE_UNICID_ON_INCLUDE_ASSETS) {
                        return Winged::$protocol . $this->folder . $this->{$field} . '?cache=' . RandomName::generate('sisisi', false, false);
                    } else {
                        return Winged::$protocol . $this->folder . $this->{$field};
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param string $field
     *
     * @return bool|string
     */
    public function getImagemPath($field = 'fb_image')
    {
        if (property_exists(get_class($this), $field)) {
            if ($this->{$field} != '') {
                if (file_exists($this->folder . $this->{$field})) {
                    return $this->folder . $this->{$field};
                }
            }
        }
        return false;
    }

    /**
     * @return $this
     */
    public function setNotFoundPage()
    {
        $this->id_seo = 'Config for controller name "' . Winged::$page_surname . '" does not match with an configured page in the DataBase';
        $this->page_title = 'Config for controller name "' . Winged::$page_surname . '" does not match with an configured page in the DataBase';
        $this->description = 'Config for controller name "' . Winged::$page_surname . '" does not match with an configured page in the DataBase';
        $this->fb_title = 'Config for controller name "' . Winged::$page_surname . '" does not match with an configured page in the DataBase';
        $this->fb_description = 'Config for controller name "' . Winged::$page_surname . '" does not match with an configured page in the DataBase';
        $this->canonical_url = 'Config for controller name "' . Winged::$page_surname . '" does not match with an configured page in the DataBase';
        $this->fb_image = 'Config for controller name "' . Winged::$page_surname . '" does not match with an configured page in the DataBase';
        $this->slug = 'Config for controller name "' . Winged::$page_surname . '" does not match with an configured page in the DataBase';
        $this->keywords = 'Config for controller name "' . Winged::$page_surname . '" does not match with an configured page in the DataBase';
        return $this;
    }

}