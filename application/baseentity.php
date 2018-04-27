<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
class BaseEntity implements \Serializable, \JsonSerializable{

    public function __construct() {
        if (method_exists($this, 'setCreated')) {
            $this->setCreated(new \DateTime());
            if ($this->getModified() == null) {
                $this->setModified(new \DateTime());
            }
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateModifiedDatetime() {
        // update the modified time
        if(method_exists($this, 'setModified')) {
            $this->setModified(new \DateTime());
        }
    }
    
    /**
     * Packs overall entity data in a very defaul way. Can (and, in fact, should) be overridden by entity method
     * 
     * @return \stdClass
     */
    public function dump() {
        $reflection = new \ReflectionClass($this);
        $vars = $reflection->getProperties();
        $dump = new \stdClass();
        foreach ($vars as $v) {
            $methodName = 'get' . ucfirst($v->getName());
            if (method_exists($this, $methodName)) {
                $dump->{$v->getName()} = $this->$methodName();
            }
        }
        return $dump;
    }

    /**
     * Implements standard method for data serialization defined in \Serializable interface
     * 
     * @return string
     */
    public function serialize() {
        $data = get_object_vars($this->dump());
        return serialize($data);
    }
    
    
    public function unserialize($data) {
//        $data = unserialize($data);
//        var_dump($data); exit;
//        $reflection = new \ReflectionClass($this);
//        $vars = $reflection->getProperties();
//        $dump = new \stdClass();
//        foreach($vars as $v){
//            $methodName = 'set' . ucfirst($v->getName());
//            if(method_exists($this, $methodName) && ) {
//                $dump->{$v->getName()} = $this->$methodName();
//            }
//        }
    }
    
    /**
     * Implements method defined in \JsonSerializable interface => it is used to pack object data into JSON string
     * 
     * @return mixed
     */
    public function jsonSerialize() {
        if(method_exists($this, 'dump')) {
            return $this->dump();
        }
        return false;
    }
    
    /**
     * Returns ID of current entity
     * Basically, is an alias for getId()
     * 
     * @see getId()
     * @return boolean
     */
    public function id() {
        if(method_exists($this, 'getId')) {
            return $this->getId();
        }
        return false;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        $now = new \DateTime();
        $this->setCreated($now);
        $this->setModified($now);
    }
    
    /**
     * @ORM\PreUpdate
     */
    public function preUpdate() {
        $this->setModified(new \DateTime());
    }

}