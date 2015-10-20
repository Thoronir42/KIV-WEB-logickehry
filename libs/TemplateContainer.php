<?php
/**
 * Description of TemplateContainer
 *
 * @author Stepan
 */
final class TemplateContainer  implements \ArrayAccess {
    
    var $data;
    
    public function __construct() {
       $this->data = []; 
    }
    
    public function __get($name) {
        if(!isset($this->data[$name])){
            throw new \InvalidArgumentException('Unknown '.__CLASS__.' array value: '.$name);
        }
        return $this->data[$name];
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        if(!isset($this->data[$offset])){
            throw new \InvalidArgumentException('Unknown '.__CLASS__.' array value with offset: '.$offset);
        }
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }
    
}
