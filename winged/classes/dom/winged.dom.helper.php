<?phpclass WingedDOMHelper{    public function isSelected(&$dom, $find)    {        $register = true;        if (array_key_exists('id', $find)) {            if ($dom->id != $find['id']) {                $register = false;            }        }        if (array_key_exists('attr', $find)) {            foreach ($find['attr'] as $attr_name => $attr) {                if (!array_key_exists($attr_name, $dom->attributes)) {                    $register = false;                    break;                }            }        }        if (array_key_exists('class', $find)) {            foreach ($find['class'] as $class_name => $class) {                if ($dom->class != null) {                    if (!in_array($class, $dom->class)) {                        $register = false;                        break;                    }                } else {                    $register = false;                    break;                }            }        }        if (array_key_exists('element', $find)) {            if ($find['element'] != $dom->tagName) {                $register = false;            }        }        return $register;    }    public function selector($selector)    {        $levels = [];        $exp = explode(' ', $selector);        foreach ($exp as $under) {            $tofind = [];            $id = explode('#', $under);            $haveid = false;            if (count($id) > 1) {                if (trim($id[0]) != "") {                    $tofind['element'] = $id[0];                }                $haveid = true;            }            $attr = $this->findAttrSelector($under);            $class = explode('.', $under);            if (count($class) >= 1 && is_int(stripos($under, '.')) && !$attr) {                foreach ($class as $c) {                    if (trim($c) != '') {                        if ($haveid) {                            $attr = $this->findAttrSelector($c);                            if ($attr) {                                $id = explode('#', $attr['args']);                                $tofind['id'] = $id[1];                                $tofind = $this->addAttrSelector($tofind, $attr['vals']);                            }                            $haveid = false;                        } else {                            if (!array_key_exists('class', $tofind)) {                                $tofind['class'] = [];                            }                            $attr = $this->findAttrSelector($c);                            if ($attr) {                                $tofind['class'][] = $attr['args'];                                $tofind = $this->addAttrSelector($tofind, $attr['vals']);                            } else {                                if (trim($class[0]) != '') {                                    if (!array_key_exists('element', $tofind)) {                                        $tofind['element'] = $c;                                    } else {                                        $tofind['class'][] = $c;                                    }                                } else {                                    $tofind['class'][] = $c;                                }                            }                        }                    }                }            } else {                if ($haveid) {                    $attr = $this->findAttrSelector($id[1]);                    if ($attr) {                        $tofind['id'] = $attr['args'];                        $tofind = $this->addAttrSelector($tofind, $attr['vals']);                    } else {                        $tofind['id'] = $id[1];                    }                } else {                    $attr = $this->findAttrSelector($under);                    if ($attr) {                        $tofind['element'] = $attr['args'];                        $tofind = $this->addAttrSelector($tofind, $attr['vals']);                    } else {                        $tofind['element'] = $under;                    }                }            }            $levels[] = $tofind;        }        $guard = false;        foreach ($levels as $key => $value) {            if (array_key_exists('element', $value)) {                if ($value['element'] == '>') {                    $guard = $value['element'];                    unset($levels[$key]);                    continue;                }            }            if ($guard) {                $levels[$key]['level'] = 1;                $guard = false;            }        }        return array_values($levels);    }    private function addAttrSelector($tofind, $attr)    {        if (!array_key_exists('attr', $tofind)) {            $tofind['attr'] = [];        }        foreach ($attr as $att) {            $tofind['attr'][$att['attr']] = $att['value'];        }        return $tofind;    }    private function findTokenAttr($str)    {        $open = 0;        $pos = [];        $valid = false;        $ret = [];        for ($x = 0; $x < strlen($str); $x++) {            if ($str[$x] == '[') {                $open++;                if (!$valid) {                    $valid = true;                }            } else if ($str[$x] == ']') {                $open--;            }            if ($open == 0 && $valid) {                $valid = false;                if (isset($str[$x + 1])) {                    if ($str[$x + 1] == '[')                        $pos[] = $x;                }            }        }        if (count($pos) > 1) {            for ($x = 0; $x < count($pos); $x++) {                if ($x == 0) {                    $ret[] = substr($str, 0, $pos[$x] + 1);                } else {                    $ret[] = substr($str, $pos[$x - 1] + 1, ($pos[$x] - ($pos[$x - 1])));                }            }            $ret[] = substr($str, end($pos) + 1, strlen($str) - 1);        } else if (count($pos) == 1) {            $ret[] = substr($str, 0, end($pos) + 1);            $ret[] = substr($str, end($pos) + 1, strlen($str) - 1);        }        return false;    }    private function findAttrSelector($str)    {        $atvl = ['args' => '', 'vals' => []];        $attrs = $this->findTokenAttr($str);        if (!$attrs) {            $attrs = explode('][', $str);        }        $attr = stripos($str, '[');        if ($attrs[0] != $str || is_int($attr)) {            for ($x = 0; $x < count($attrs); $x++) {                $str = $attrs[$x];                if ($x == 0) {                    $attr = stripos($str, '[');                    if (is_int($attr)) {                        $sep = substr($str, 0, $attr);                        $to = substr($str, $attr, strlen($str) - 1);                        if ($to[0] == '[') {                            $to[0] = '';                        }                        if ($to[strlen($to) - 1] == ']') {                            $to[strlen($to) - 1] = '';                        }                        $to = trim($to);                        $exp = explode('=', $to);                        if (count($exp) > 1) {                            $attr = array_shift($exp);                            $atvl['args'] = $sep;                            $atvl['vals'][] = ['attr' => $attr, 'value' => implode('=', $exp)];                        } else {                            Winged::push_warning(__CLASS__, 'Can\'t recognize attr selector for expression => ' . $str . '', true);                        }                    } else {                        Winged::push_warning(__CLASS__, 'Can\'t recognize attr selector for expression => ' . $str . '', true);                    }                } else {                    if ($str[0] == '[') {                        $str[0] = '';                    }                    if ($str[strlen($str) - 1] == ']') {                        $str[strlen($str) - 1] = '';                    }                    $str = trim($str);                    $exp = explode('=', $str);                    if (count($exp) > 1) {                        $attr = array_shift($exp);                        $atvl['vals'][] = ['attr' => $attr, 'value' => implode('=', $exp)];                    } else {                        Winged::push_warning(__CLASS__, 'Can\'t recognize attr selector for with expression => ' . $str . '', true);                    }                }            }            return $atvl;        }        return false;    }    public function compareElement($subject = null){        if($subject != null){                    }    }}