<?php

/**
 * @author Andreas Treichel <gmblar+github@gmail.com>
 */

namespace Blar\Ldap;

use Blar\Common\Collections\Collection;

class LdapEntryList extends Collection {

    public function __toString() {
        return $this->getLdif();
    }

    public function getLdif() {
        return $this->join("\n");
    }

}
