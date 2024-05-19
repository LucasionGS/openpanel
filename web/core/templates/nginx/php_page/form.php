<?php
use OpenPanel\core\webhost\HostAddonForm;

function form(HostAddonForm $form) {
  $form->addInput("php_version", "PHP Version", "text", "8.1");
}