<?php

/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Ldap;

class LdapEntry {

    public $ldapHandle;
    public $entryHandle;

    public function __toString() {
        return $this->getLdif();
    }

    public function __get($propertyName) {
        return $this->get($propertyName);
    }

    public function get($attributeName) {
        $values = @ldap_get_values($this->ldapHandle, $this->entryHandle, $attributeName);
        return $this->convertAttributeValues($values);
    }

    public function getDn() {
        return ldap_get_dn($this->ldapHandle, $this->entryHandle);
    }

    public function getAttributeNames() {
        $attributes = array();
        $attributes[] = ldap_first_attribute($this->ldapHandle, $this->entryHandle);
        while($attribute = ldap_next_attribute($this->ldapHandle, $this->entryHandle)) {
            $attributes[] = $attribute;
        }
        return $attributes;
    }

    public function getAttributes() {
        $result = array();
        foreach($this->getAttributeNames($this->entryHandle) as $attributeName) {
            $values = ldap_get_values($this->ldapHandle, $this->entryHandle, $attributeName);
            $result[$attributeName] = $this->convertAttributeValues($values);
        }
        return $result;
    }

    public function convertAttributeValues($values) {
        if($values === FALSE) {
            return NULL;
        }
        if($values['count'] == 1) {
            $values = $values[0];
        }
        else {
            unset($values['count']);
        }
        return $values;
    }

    public function compare($attributeName, $needle) {
        return ldap_compare($this->ldapHandle, $this->getDn(), $attributeName, $needle);
    }

    public function getLdif() {
        $result = sprintf("dn: %s\n", $this->getDn());
        foreach($this->getAttributes() as $attributeName  => $attribute) {
            if(!is_array($attribute)) {
                $attribute = array($attribute);
            }
            foreach($attribute as $value) {
                if(ctype_print($value)) {
                    $value = wordwrap($value, 75, "\n\t", TRUE);
                    $result .= sprintf("%s: %s\n", $attributeName, $value);
                }
                else {
                    $value = wordwrap(base64_encode($value), 75, "\n\t", TRUE);
                    $result .= sprintf("%s:: %s\n", $attributeName, $value);
                }
            }
        }
        return $result;
    }
}
