<?php

/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Ldap;

use PHPUnit_Framework_TestCase as TestCase;

class LdapTest extends TestCase {

    public function getLdap() {
        $ldap = new Ldap('127.0.0.1');
        $ldap->setOption(LDAP_OPT_PROTOCOL_VERSION, 3);
        $ldap->bind('cn=Manager, dc=foobox, dc=de', 'test');
        return $ldap;
    }

    public function testLdif() {
        $ldap = $this->getLdap();
        $result = $ldap->search('ou=users, dc=foobox, dc=de', '(objectClass=*)');
        # echo $result;
        foreach($result as $entry) {
            var_dump($entry->getDn());
            var_dump($entry->cn);
        }
        die();
    }
}
