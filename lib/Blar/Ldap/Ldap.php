<?php

namespace Blar\Ldap;

class Ldap {

    protected $handle;

    public function __construct($hostName = NULL, $port = 389) {
        $this->setHandle(ldap_connect($hostName, $port));
    }

    public function __destruct() {
        $this->unbind();
    }

    /**
     * @param mixed $handle
     * @return $this
     */
    public function setHandle($handle) {
        $this->handle = $handle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHandle() {
        return $this->handle;
    }

    public function setOption($option, $value) {
        ldap_set_option($this->getHandle(), $option, $value);
        return $this;
    }

    public function getOption($option) {
        ldap_get_option($this->getHandle(), $option, $result);
        return $result;
    }

    public function bind($rdn = NULL, $password = NULL) {
        ldap_bind($this->getHandle(), $rdn, $password);
        return $this;
    }

    public function unbind() {
        ldap_unbind($this->getHandle());
        return $this;
    }

    public function getItems($search) {
        $items = array();

        $item = ldap_first_entry($this->getHandle(), $search);
        $dn = ldap_get_dn($this->getHandle(), $item);
        $items[$dn] = $item;

        while($item = ldap_next_entry($this->getHandle(), $item)) {
            $dn = ldap_get_dn($this->getHandle(), $item);
            $items[$dn] = $item;
        }
        return $items;
    }

    public function getAttributeNames($item) {
        $attributeNames = array();

        $attributeNames[] = ldap_first_attribute($this->getHandle(), $item);
        while($attribute = ldap_next_attribute($this->getHandle(), $item)) {
            $attributeNames[] = $attribute;
        }
        return $attributeNames;
    }

    public function getAttributes($item) {
        $result = array();
        foreach($this->getAttributeNames($item) as $attribute) {
            $values = ldap_get_values($this->getHandle(), $item, $attribute);
            $result[$attribute] = $this->convertAttributeValues($values);
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

    public function fetch($result) {
        $ldapEntryList = new LdapEntryList();
        foreach($this->getItems($result) as $item) {
            $entry = new LdapEntry();
            $entry->ldapHandle = $this->getHandle();
            $entry->entryHandle = $item;
            $ldapEntryList[] = $entry;
        }
        return $ldapEntryList;
    }

    public function read($base, $filter, $attributes = array()) {
        $result = ldap_read($base, $filter, $attributes);
        return $this->fetch($result);
    }

    public function getEntries($base, $filter, $attributes = array()) {
        $result = ldap_list($this->getHandle(), $base, $filter, $attributes);
        return $this->fetch($result);
    }

    public function search($base, $filter, $attributes = array()) {
        $result = ldap_search($this->getHandle(), $base, $filter);
        return $this->fetch($result);
    }

    public function add($dn, $entry) {
        ldap_add($this->getHandle(), $dn, $entry);
        return $this;
    }

    public function modify($dn, $entry) {
        ldap_add($this->getHandle(), $dn, $entry);
        return $this;
    }

    public function addAttributes($dn, $attributes) {
        ldap_mod_add($this->getHandle(), $dn, $attributes);
        return $this;
    }

    public function removeAttribute($dn, $attribute, $values = array()) {
        $this->removeAttributes($dn, array($attribute => $values));
        return $this;
    }

    public function removeAttributes($dn, $attributes = array()) {
        ldap_mod_del($this->getHandle(), $dn, $attributes);
        return $this;
    }

    public function remove($dn) {
        ldap_delete($this->getHandle(), $dn);
        return $this;
    }

    public function compare($dn, $attribute, $needle) {
        return ldap_compare($this->getHandle(), $dn, $attribute, $needle);
    }

}

