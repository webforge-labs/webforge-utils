<?php

namespace Webforge\Common\Auth;

interface System {

  public function login($ident, $plaintextPassword, $permanent = FALSE);

  public function validate();
}
